<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class PerformanceController extends Controller{

        public function obtener_evaluaciones(Request $request){

            $colaboradores = app('db')->select("    SELECT *
                                                    FROM RH_COLABORADORES_TEMPORADA
                                                    WHERE CODAREA = '8'
                                                    AND TEMPORADAID = '20'");
            
            foreach ($colaboradores as &$colaborador) {
                
                // Por cada colaborador buscar la evaluación de superior
                $evaluacion_superior = app('db')->select("  SELECT *
                                                            FROM RH_RESULTADOS_TEMPORADA
                                                            WHERE TEMPORADAID = '20'
                                                            AND NIT = '$colaborador->nit'
                                                            AND TIPOEV = 'superior'");

                $colaborador->superior = $evaluacion_superior;

                // Obtener el promedio de calificacion

                $total = 0;

                foreach ($evaluacion_superior as $item) {
                    
                    $total += $item->calificacion;

                }

                $colaborador->superior_promedio = $total / count($evaluacion_superior);

                // Colega
                $evaluacion_colega = app('db')->select("    SELECT *
                                                            FROM RH_RESULTADOS_TEMPORADA
                                                            WHERE TEMPORADAID = '20'
                                                            AND NIT = '$colaborador->nit'
                                                            AND TIPOEV = 'colega'");

                $colaborador->colega = $evaluacion_colega;

                // Obtener el promedio de calificación

                $total = 0;

                foreach ($evaluacion_colega as $item) {
                    
                    $total += $item->calificacion;

                }

                if ($evaluacion_colega) {
                    
                    $colaborador->colega_promedio = $total / count($evaluacion_colega);

                }else{

                    $colaborador->colega_promedio = 0;

                }

                // Sacar el total de la calificación

                $colaborador->calificacion = round((($colaborador->colega_promedio * $colaborador->porcentaje_colega) / 100) + (($colaborador->superior_promedio * $colaborador->porcentaje_asesor) / 100), 2);

                // Asignar un color

                if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {
                        
                    $colaborador->color = 'red';

                }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){

                    $colaborador->color = 'orange';

                }else{

                    $colaborador->color = 'green';

                }
                
            }

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "nombre",
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
                "headers" => $headers,
                "items" => $colaboradores
            ];
            return response()->json($data);

        }

        public function obtener_temporadas(Request $request){

            $temporadas = app('db')->select("   SELECT *
                                                FROM TEMPORADAS
                                                ORDER BY TEMPORADAID DESC");

            return response()->json($temporadas);

        }

    }
