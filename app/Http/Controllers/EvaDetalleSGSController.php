<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\EvaluacionSGS;
    use App\Area;
    use App\Empleado;

    class EvaDetalleSGSController extends Controller{

        public function actividades_disponibles(Request $request){

            $actividades = app('db')->select("  SELECT *
                                                FROM RRHH_IND_SGS_ACTIVIDAD
                                                WHERE ID NOT IN (
                                                    SELECT ID_ACTIVIDAD
                                                    FROM RRHH_IND_SGS_EVA_ACT
                                                    WHERE ID_EVALUACION = '$request->id_evaluacion'
                                                )
                                                AND DELETED_AT IS NULL");

            foreach ($actividades as &$actividad) {
                $actividad->check = false;
            }

            return response()->json($actividades);

        }

        public function agregar_actividades(Request $request){

            foreach ($request->actividades as $actividad) {
                
                $actividad_ = (object) $actividad;

                $result = app('db')->table('RRHH_IND_SGS_EVA_ACT')->insert([
                    'id_evaluacion' => $request->id_evaluacion,
                    'id_actividad' => $actividad_->id
                ]);

            }

            return response()->json($result);

        }

        public function actividades_evaluacion(Request $request){

            $actividades = app('db')->select("  SELECT T2.*, T1.NOMBRE
                                                FROM RRHH_IND_SGS_ACTIVIDAD T1
                                                INNER JOIN RRHH_IND_SGS_EVA_ACT T2
                                                ON T1.ID = T2.ID_ACTIVIDAD
                                                WHERE T2.ID_EVALUACION = $request->id_evaluacion");

            foreach ($actividades as &$actividad) {
                $actividad->check = false;
            }

            return response()->json($actividades);

        }

        public function asignar_responsables(Request $request){

            foreach ($request->responsables as $responsable) {
                
                $result = app('db')->table('RRHH_IND_SGS_EVA_ACT_RESP')->insert([
                    'responsable' => $responsable,
                    'id_actividad_evaluacion' => $request->actividad,
                    'realizada' => 'S'
                ]);

            }

            $response = [
                "status" => 200
            ];

            return response()->json($response);

        }

        public function obtener_responsables_actividad(Request $request){

            $responsables = app('db')->select(" SELECT 
                                                    RESPONSABLE AS NIT
                                                FROM RRHH_IND_SGS_EVA_ACT_RESP
                                                WHERE ID_ACTIVIDAD_EVALUACION = $request->id_actividad");

            $arr_responsables = [];

            foreach ($responsables as $responsable) {
                
                $arr_responsables [] = "'" .$responsable->nit . "'";

            }

            $str_responsables = implode(",", $arr_responsables);

            if (!$responsables) {
                
                return response()->json([]);
                
            }

            $areas = app('db')->select("SELECT *
                                        FROM RH_AREAS
                                        WHERE CODAREA IN (
                                            SELECT DISTINCT(CODAREA) AS CODAREA
                                            FROM RH_EMPLEADOS 
                                            WHERE NIT IN ($str_responsables)
                                            AND STATUS = 'A'
                                        )");

            foreach ($areas as $area) {
                
                $area->check = false;
                $area->expand = false;

                $empleados = app('db')->select("SELECT T1.*, T2.REALIZADA
                                                FROM RH_EMPLEADOS T1
                                                INNER JOIN RRHH_IND_SGS_EVA_ACT_RESP T2
                                                ON T1.NIT = T2.RESPONSABLE
                                                WHERE T1.CODAREA = '$area->codarea'
                                                AND T1.NIT IN ($str_responsables)
                                                AND T1.STATUS = 'A'");

                foreach ($empleados as &$empleado) {
                    $empleado->check = false;
                }

                $area->empleados = $empleados;

            }

            return response()->json($areas);

        }

        public function colaboradores_disponibles(Request $request){

            $responsables = app('db')->select(" SELECT 
                                                    RESPONSABLE AS NIT
                                                FROM RRHH_IND_SGS_EVA_ACT_RESP
                                                WHERE ID_ACTIVIDAD_EVALUACION = $request->id_actividad");

            if(!$responsables){

                $areas = Area::where('estatus', 'A')->get();

                foreach ($areas as &$area) {
                    
                    $area->check = false;
    
                    $empleados = Empleado::where('codarea', $area->codarea)->where('status', 'A')->get();
    
                    foreach ($empleados as &$empleado) {
                        
                        $empleado->check = false;

                    }
                    
                    $area->empleados = $empleados;

                    $area->expand = false;

                }
                
                return response()->json($areas);

            }

            $arr_responsables = [];

            foreach ($responsables as $responsable) {
                
                $arr_responsables [] = "'" .$responsable->nit . "'";

            }

            $str_responsables = implode(",", $arr_responsables);

            // Obtener las áreas de las personas que ya han sido agregadas como responsables 
            $areas_responsables = app('db')->select("   SELECT 
                                                            DISTINCT(CODAREA) AS CODAREA
                                                        FROM RH_EMPLEADOS 
                                                        WHERE NIT IN ($str_responsables)");

            $areas_excluir = [];

            foreach ($areas_responsables as &$area) {
                
                $empleados_restantes = app('db')->select("  SELECT 
                                                                COUNT(*) AS TOTAL
                                                            FROM RH_EMPLEADOS
                                                            WHERE CODAREA = '$area->codarea'
                                                            AND NIT NOT IN ($str_responsables)
                                                            AND STATUS = 'A'");

                if($empleados_restantes[0]->total <= 0){

                    $areas_excluir [] = $area->codarea;

                }

            }

            $str_areas_excluir = implode(",", $areas_excluir);

            if (!$str_areas_excluir) {
                
                $areas = app('db')->select("SELECT *
                                            FROM RH_AREAS
                                            WHERE ESTATUS = 'A'");

            }else{

                $areas = app('db')->select("SELECT *
                                            FROM RH_AREAS
                                            WHERE CODAREA NOT IN ($str_areas_excluir)
                                            AND ESTATUS = 'A'");

            }

            foreach ($areas as $area) {
                
                $area->check = false;
                $area->expand = false;

                $empleados = app('db')->select("SELECT *
                                                FROM RH_EMPLEADOS
                                                WHERE CODAREA = '$area->codarea'
                                                AND NIT NOT IN ($str_responsables)
                                                AND STATUS = 'A'");

                foreach ($empleados as &$empleado) {
                    $empleado->check = false;
                }

                $area->empleados = $empleados;

            }

            return response()->json($areas);

        }

        public function actualizar_cumplimiento(Request $request){

            $result = app('db')
                        ->table('RRHH_IND_SGS_EVA_ACT_RESP')
                        ->where('responsable', $request->responsable)
                        ->where('id_actividad_evaluacion', $request->actividad)->update([
                            'realizada' => $request->realizada
                        ]);
            
            if (!$result) {
                
                $response = [
                    "status" => 100
                ];

                return repsonse()->json($response);
            }

            $response = [
                "status" => 200
            ];

            return response()->json($response);

        }

        public function actualizar_porcentaje(Request $request){

            $result = app('db')
                        ->table('RRHH_IND_SGS_EVA_ACT')
                        ->where('id', $request->id)
                        ->update([
                            'porcentaje' => $request->porcentaje
                        ]);

            return response()->json($result);

        }

        public function eliminar_responsables(Request $request){

            foreach ($request->responsables as $responsable) {
                
                $result = app('db')
                            ->table('RRHH_IND_SGS_EVA_ACT_RESP')
                            ->where('responsable', $responsable)
                            ->where('id_actividad_evaluacion', $request->actividad)
                            ->delete();

            }

            $response = [
                "status" => 200
            ];

            return response()->json($response);

        }

    }

?>