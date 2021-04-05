<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\Evaluacion;
    use App\DetalleEvaluacion;
    use App\Menu;
    use App\Permiso;

    class EvaluacionController extends Controller{

        public function registrar_evaluacion(Request $request){

            $evaluacion = new Evaluacion();
            $evaluacion->id_criterio = $request->criterio["id"];
            $evaluacion->id_persona = $request->id_persona;
            $evaluacion->valor_criterio = $request->criterio["valor"];
            $evaluacion->save();

            //$criterio = Criterio::where('modulo', $request->url)->first();

            $criterios = $request->items;

            foreach ($criterios as &$criterio) {
                
                $detalle_evaluacion = new DetalleEvaluacion();
                $detalle_evaluacion->id_evaluacion = $evaluacion->id;
                $detalle_evaluacion->id_item = $criterio["id"];

                if ($request->criterio["division"] == 'S') {
                    
                    $detalle_evaluacion->calificacion = ($criterio["valor"] * $criterio["calificacion"]) / 100;

                }else {

                    $detalle_evaluacion->calificacion = $criterio["valor"] * $criterio["calificacion"];

                }
                
                $detalle_evaluacion->save();

            }

            $data = [
                "title" => "Excelente",
                "message" => "La evaluación a sido registrada exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function obtener_evaluaciones(Request $request){

            $criterio = Criterio::where('modulo', $request->url)->first();
            
            // Dependiendo del permiso mostrar todas las secciones o no
            $menu = Menu::where('url', $request->url)->first();

            $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu', $menu->id)->first();

            if ($permiso->secciones == 'S') {
                
                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_aT,
                                                        T1.VALOR_CRITERIO
                                                    FROM RRHH_IND_EVALUACION T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T1.ID_CRITERIO = $criterio->id
                                                    ORDER BY T1.ID DESC");

            }else{

                // Buscar solo las evaluaciones de la sección del usuario
                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT,
                                                        T1.VALOR_CRITERIO
                                                    FROM RRHH_IND_EVALUACION T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T1.ID_CRITERIO = $criterio->id
                                                    AND T2.CODAREA = $request->codarea
                                                    ORDER BY T1.ID DESC");

            }

            foreach ($evaluaciones as $evaluacion) {
                
                // Obtener el detalle de la evaluación
                $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                $total = 0;

                foreach ($detalle as $item) {
                    
                    $total += $item->calificacion;

                }

                $evaluacion->calificacion = round(($total / $evaluacion->valor_criterio) * 100, 2);

                $evaluacion->calificacion = $evaluacion->calificacion > 100 ? 100 : $evaluacion->calificacion;

                if ($evaluacion->calificacion >= 0 && $evaluacion->calificacion < 60) {
                   
                    $evaluacion->color = 'red';

                }elseif( $evaluacion->calificacion >= 60 && $evaluacion->calificacion < 80){

                    $evaluacion->color = 'orange';

                }else{

                    $evaluacion->color = 'green';

                }

            }

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "colaborador",
                    "width" => "40%"
                ],
                [
                    "text" => "Fecha",
                    "value" => "created_at",
                    "width" => "30%"
                ],
                [
                    "text" => "Calificación",
                    "value" => "calificacion",
                    "width" => "20%",
                    "align" => "center",
                    "sortable" => false
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "sortable" => false,
                    "align" => "right",
                    "width" => "10%"
                ]
            ];

            $data = [
                "items" => $evaluaciones,
                "headers" => $headers,
                "criterio" => $criterio
            ];

            return response()->json($data);

        }

        public function eliminar_evaluacion(Request $request){

            $evaluacion = Evaluacion::find($request->id);
            $result = $evaluacion->delete();

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "La evaluación a sido eliminada exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function editar_evaluacion(Request $request){

            // Eliminar los registros anteriores de la evaluación
            foreach ($request->items as $item) {
                
                //$detalle_evaluacion = DetalleEvaluacion::where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item["id"])->first();

                $calificacion = 0;

                if ($request->criterio["division"] == 'S') {

                    $calificacion = ($item["valor"] * $item["calificacion"]) / 100;

                }else{

                    $calificacion = $item["valor"] * $item["calificacion"];

                }

                $result = app('db')->table('rrhh_ind_evaluacion_detalle')->where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item["id"])->update(['calificacion' => $calificacion]);


            }

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "La evaluación a sido actualizada exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

    }

?>