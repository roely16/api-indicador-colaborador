<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\Evaluacion;
    use App\DetalleEvaluacion;

    class EvaluacionController extends Controller{

        public function registrar_evaluacion(Request $request){

            $evaluacion = new Evaluacion();
            $evaluacion->id_criterio = $request->criterio["id"];
            $evaluacion->id_persona = $request->id_persona;
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
            
            $evaluaciones = app('db')->select(" SELECT 
                                                    T1.ID, 
                                                    CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                    TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_aT
                                                FROM RRHH_IND_EVALUACION T1
                                                INNER JOIN RH_EMPLEADOS T2
                                                ON T1.ID_PERSONA = T2.NIT
                                                WHERE T1.ID_CRITERIO = $criterio->id
                                                ORDER BY T1.ID DESC");

            foreach ($evaluaciones as $evaluacion) {
                
                // Obtener el detalle de la evaluación
                $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                $total = 0;

                foreach ($detalle as $item) {
                    
                    $total += $item->calificacion;

                }

                $evaluacion->calificacion = round(($total / $criterio->valor) * 100, 2);

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

    }

?>