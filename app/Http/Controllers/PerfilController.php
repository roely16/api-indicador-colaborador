<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Perfil;
    use App\Competencia;
    use App\Empleado;
    use App\TipoCompetencia;
    use App\Area;

    class PerfilController extends Controller{

        public function obtener_perfiles(Request $request){

            $areas = Area::where('estatus', 'A')->get();

            foreach ($areas as &$area) {
                
                $jefe = Empleado::where('codarea', $area->codarea)->where('jefe', '1')->where('status', 'A')->first();

                $area->jefe = $jefe;

                // Buscar los perfiles 

                $perfiles = app('db')->select(" SELECT DISTINCT(ID_PERFIL) AS ID_PERFIL
                                                FROM RH_EMPLEADO_PERFIL
                                                WHERE NIT IN (
                                                    
                                                    SELECT NIT
                                                    FROM RH_EMPLEADOS
                                                    WHERE CODAREA = '$area->codarea'
                                                    AND STATUS = 'A'
                                                
                                                )");

                $_perfiles = [];

                foreach ($perfiles as $perfil) {
                    
                    $perfil_ = Perfil::find($perfil->id_perfil);

                    if($perfil_){

                        // Buscar la cantidad de colaboradores con dicho perfil
                        $num_colaboradores = app('db')->select("    SELECT COUNT(*) AS TOTAL
                                                                    FROM RH_EMPLEADO_PERFIL
                                                                    WHERE ID_PERFIL = $perfil_->id");

                        $perfil_->colaboradores = $num_colaboradores ? $num_colaboradores[0]->total : 0;

                        $_perfiles [] = $perfil_;

                    }
                    

                }

                $area->perfiles = $_perfiles;

            }

            $perfiles_sin_asignar = app('db')->select(" SELECT *
                                                        FROM RRHH_PERFIL
                                                        WHERE ID NOT IN (
                                                            SELECT ID_PERFIL
                                                            FROM RH_EMPLEADO_PERFIL
                                                        )
                                                        AND DELETED_AT IS NULL");

            foreach ($perfiles_sin_asignar as $perfil) {
                
                $perfil->colaboradores = 0;

            }

            $sin_asignar = [
                [
                    "codarea" => 9999999,
                    "descripcion" => "Pendientes de Asignar Colaborador",
                    "perfiles" => $perfiles_sin_asignar
                ]
            ];

            //$areas [] = $sin_asignar;

            //array_unshift($areas, $sin_asignar);

            $response = [
                "areas" => $areas,
                "sin_asignar" => $sin_asignar
            ];

            return response()->json($response);
            
        }

        public function registrar(Request $request){

            $perfil = new Perfil();

            $perfil->nombre = $request->nombre;
            $perfil->descripcion = $request->descripcion;
            $perfil->save();

            // Registrar las competencias
            foreach ($request->tipos_competencias as $tipo) {
                
                foreach ($tipo["competencias"] as $competencia) {
                    
                    //return response()->json($competencia["nombre"]);

                    $n_competencia = new Competencia();
                    
                    $n_competencia->nombre = $competencia["nombre"];
                    $n_competencia->id_tipo = $competencia["id_tipo"];
                    $n_competencia->id_perfil = $perfil->id;

                    $n_competencia->save();

                }

            }

            // Asignar el perfil
            foreach ($request->colaboradores as $colaborador) {
                
                $result = app('db')->table('RH_EMPLEADO_PERFIL')
                            ->insert(
                                [
                                    'nit' => $colaborador["nit"],
                                    'id_perfil' => $perfil->id
                                ]
                            );

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "El perfil a sido registrado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function detalle(Request $request){

            $perfil = Perfil::find($request->id);
            $perfil->mostrar_nuevo = false;
            $perfil->nueva_competencia = [
                "nombre" => null,
                "tipo" => null
            ];

            // Buscar las competencias 

            $arr_tipos_competencias = [];

            $tipos_competencias = TipoCompetencia::all();

            foreach ($tipos_competencias as $tipo) {
                
                $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $perfil->id)->get();

                if(count($competencias) > 0){

                    foreach ($competencias as &$competencia) {

                        $competencia->edit = false;
                        $competencia->delete = false;

                    }

                    $tipo->competencias = $competencias;
                    $arr_tipos_competencias [] = $tipo;

                }

            }

            $perfil->tipos_competencias = $arr_tipos_competencias;

            //$perfil->colaboradores = Empleado::where('id_perfil', $perfil->id)->get();

            $perfil->colaboradores = app('db')->select("    SELECT T1.*, T2.ID_PERFIL
                                                            FROM RH_EMPLEADOS T1
                                                            INNER JOIN RH_EMPLEADO_PERFIL T2
                                                            ON T1.NIT = T2.NIT
                                                            WHERE T2.ID_PERFIL = $perfil->id");

            foreach ($perfil->colaboradores as $colaborador) {
                
                $colaborador->delete = false;

            }

            return response()->json($perfil);

        }

        public function editar(Request $request){

            $perfil = Perfil::find($request->id);

            $perfil->nombre = $request->nombre;
            $perfil->descripcion = $request->descripcion;

            $perfil->save();
            
            // Actualizar o registrar las nuevas competencias
            foreach ($request->tipos_competencias as $tipo) {
                
                foreach ($tipo["competencias"] as $competencia) {

                    $obj_competencia = (object) $competencia;

                    if (!property_exists($obj_competencia, 'delete')) {
                        
                        $obj_competencia->delete = false;

                    }

                    if (!property_exists($obj_competencia, 'id')) {
                        
                        $obj_competencia->id = null;

                    }

                    // Validar si se tiene que eliminar
                    if ($obj_competencia->delete === true) {
                        
                        $find_competencia = Competencia::find($obj_competencia->id);
                        $find_competencia->delete();

                    }elseif($obj_competencia->id){

                        // Validar si se actualiza

                        $result = app('db')->table("RRHH_COMPETENCIA")->where('id', $obj_competencia->id)->update(["nombre" => $obj_competencia->nombre]);

                        

                    }else{

                        $n_competencia = new Competencia();
                    
                        $n_competencia->nombre = $obj_competencia->nombre;
                        $n_competencia->id_tipo = $obj_competencia->id_tipo;
                        $n_competencia->id_perfil = $perfil->id;

                        $n_competencia->save();

                    }


                }

            }

            foreach ($request->colaboradores as $colaborador) {
                
                $obj_colaborador = (object) $colaborador;

                if (!property_exists($obj_colaborador, 'delete')) {
                        
                    $obj_colaborador->delete = false;

                }

                if (!$obj_colaborador->id_perfil) {
                        
                    // Asignar
                    $result = app('db')->table("RH_EMPLEADOS")->where('nit', $obj_colaborador->nit)->update(["id_perfil" => $perfil->id]);

                }

                if ($obj_colaborador->delete === true) {
                    
                    $result = app('db')->table("RH_EMPLEADOS")->where('nit', $obj_colaborador->nit)->update(["id_perfil" => null]);

                }

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "El perfil a sido actualizado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function eliminar(Request $request){

            $perfil = Perfil::find($request->id);
            $result = $perfil->delete();

            if ($result) {
                
                /*
                    Eliminar las personas asignadas a dicho perfil 
                */

                $result = app('db')->table('RH_EMPLEADO_PERFIL')->where('id_perfil', $request->id)->delete();

                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "El perfil a sido eliminado exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function info_colaborador(Request $request){

            $colaborador = Empleado::where('nit', $request->nit)->first();

            return response()->json($colaborador);

        }

        public function eliminar_colaborador_perfil(Request $request){

            $result = app('db')
                        ->table('RH_EMPLEADO_PERFIL')
                        ->where('nit', $request->nit)
                        ->where('id_perfil', $request->id_perfil)
                        ->delete();

            if ($result) {
                
                $data = [
                    "status" => 200
                ];

            }

            return response()->json($data);

        }

        public function agregar_colaborador_perfil(Request $request){

            $result = app('db')
                        ->table('RH_EMPLEADO_PERFIL')
                        ->insert([
                            [
                                'nit' => $request->nit,
                                'id_perfil' => $request->id_perfil
                            ]
                        ]);

            if ($result) {
                
                $response = [
                    "status" => 200
                ];

            }

            return response()->json($response);

        }

    }