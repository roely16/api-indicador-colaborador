<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Perfil;
    use App\Competencia;
    use App\Empleado;
    use App\TipoCompetencia;

    class PerfilController extends Controller{

        public function obtener_perfiles(Request $request){

            $items = app('db')->select("    SELECT 
                                                ID, 
                                                NOMBRE, 
                                                TO_CHAR(CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT, 
                                                DESCRIPCION
                                            FROM RRHH_PERFIL
                                            WHERE DELETED_AT IS NULL");

            // Contar cuantos colaboradores tienen asignado el perfil

            foreach ($items as $item) {
                
                $result = app('db')->select("    SELECT COUNT(*) AS TOTAL
                                                FROM RH_EMPLEADOS
                                                WHERE ID_PERFIL = $item->id");

                $item->colaboradores = intval($result[0]->total);

            }

            $headers = [
                [
                    "text" => "Perfil",
                    "value" => "nombre",
                    "sortable" => false,
                    "width" => "40%"
                ],
                [
                    "text" => "Fecha de Creación",
                    "value" => "created_at",
                    "width" => "20%"
                ],
                [
                    "text" => "No. de Colaborador",
                    "value" => "colaboradores",
                    "width" => "20%"
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "align" => "end",
                    "width" => "20%"
                ]
            ];

            $data = [
                "items" => $items,
                "headers" => $headers
            ];

            return response()->json($data);
            
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
                
                $result = app('db')->table('RH_EMPLEADOS')->where('nit', $colaborador["nit"])->update(['id_perfil' => $perfil->id]);

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

            $perfil->colaboradores = Empleado::where('id_perfil', $perfil->id)->get();

            foreach ($perfil->colaboradores as &$colaborador) {
                
                $colaborador->delete = false;

            }

            return response()->json($perfil);

        }

        public function editar(Request $request){

            $perfil = Perfil::find($request->id);

            $perfil->nombre = $request->nombre;
            $perfil->descripcion = $request->descripcion;

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

    }