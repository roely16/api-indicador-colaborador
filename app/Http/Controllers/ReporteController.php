<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;
    use App\Ponderacion;
    use App\Empleado;
    use App\Area;
    use App\Evaluacion;
    use App\DetalleEvaluacion;

    class ReporteController extends Controller{

        public function obtener_reportes(Request $request){

            $items = [];

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "colaborador"
                ],
                [
                    "text" => "Fecha",
                    "value" => "fecha"
                ],
                [
                    "text" => "Calificación",
                    "value" => "calificacion"
                ],
                [
                    "text" => "Acción",
                    "value" => "action"
                ]
            ];

            $data = [
                "items" => $items,
                "headers" => $headers
            ];

            return response()->json($data);

        }

        public function datos_reporte(Request $request){

            // Obtener la información del colaborador
            $colaborador = Empleado::where('nit', $request->nit)->first();

            // Obtener la información de la sección
            $area = Area::find($colaborador->codarea);

            $criterio = Criterio::where('modulo', $request->url)->first();

            $criterio->valor = $area->iso == '1' ? $criterio->valor : $criterio->valor_no_iso;

            // Obtener los criterios dependiendo si es asesor o colaborador

            if ($colaborador->jefe == '1') {
                
                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_ASESOR = 'S'
                                                AND T1.DELETED_AT IS NULL
                                                ORDER BY T1.ID ASC");

            }else{

                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_PRESTADOR = 'S'
                                                AND T1.DELETED_AT IS NULL
                                                ORDER BY T1.ID ASC");

            }

            foreach ($items as $item) {
                
                // Si el metodo de evaluación es ponderación
                if ($criterio->metodo_calificacion == 'ponderacion') {
                    
                    $item->calificaciones = Ponderacion::where('id_criterio_item', $item->id)->orderBy('valor', 'desc')->get();

                    $item->calificacion = null;

                }else{

                    // Validar si se obtiene la calificación desde otra función

                    if ($item->funcion_calculo) {
                        
                        $data = [
                            "usuario" => $colaborador->usuario,
                            "usuario2" => $colaborador->usuario_2,
                            "nit" => $colaborador->nit,
                            "month" => $request->month,
                            "codarea" => $colaborador->codarea,
                            "id_item" => $item->id
                        ];

                        $result = app("App\Http\Controllers" . $item->controlador)->{$item->funcion_calculo}($data);

                        $item->calificacion = $result["calificacion"];
                        $item->editable = $result["editable"];
                        $item->info_calculo = $result["info_calculo"];
                        $item->motivos = $result["motivos"];      
                        $item->data_calculo = array_key_exists('data_calculo', $result) ? $result["data_calculo"] : null;                  

                    }else{

                        $item->calificacion = 100;
                        $item->edit = true;
                        $item->editable = true;

                    }

                    $item->comentario = null;

                }

                /* 
                    Para obtener el valor de cada ítem dividir el
                    número de ítems entre el valor del criterio 
                    dependiendo si es ISO o NO ISO
                */

                // Especificar cual será el valor
                if ($area->iso == '1') {
                    
                    // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                    //$item->valor = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                    $item->valor = round($criterio->valor / count($items), 2);

                }else{

                    // Si no es ISO asignar 
                    //$item->valor = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

                    $item->valor = round($criterio->valor_no_iso / count($items), 2);

                }


                $item->check = false;
                $item->show_description = false;

            }

            $data = [
                "criterio" => $criterio,
                "items" => $items,
                "detalle_colaborador" => [
                    "iso" => $area->iso,
                    "asesor" => $colaborador->jefe
                ]
            ];

            return response()->json($data);

        }

        public function detalle_reporte(Request $request){

            // Obtener la información del colaborador
            $colaborador = Empleado::where('nit', $request->nit)->first();

            // Obtener la información de la sección
            $area = Area::find($colaborador->codarea);
            
            // Obtener la evaluación
            $evaluacion = Evaluacion::find($request->id_evaluacion);

            // Obtener el criterio
            $criterio = Criterio::find($evaluacion->id_criterio);
            $criterio->valor = $area->iso == '1' ? $criterio->valor : $criterio->valor_no_iso;

            // Obtener los elementos a calificar
            if ($colaborador->jefe == '1') {
                
                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_ASESOR = 'S'
                                                AND T1.DELETED_AT IS NULL
                                                ORDER BY T1.ID ASC");

            }else{

                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_PRESTADOR = 'S'
                                                AND T1.DELETED_AT IS NULL
                                                ORDER BY T1.ID ASC");

            }

            foreach ($items as $item) {
                
                // Si el metodo de evaluación es ponderación
                if ($criterio->metodo_calificacion == 'ponderacion') {
                    
                    $item->calificaciones = Ponderacion::where('id_criterio_item', $item->id)->orderBy('valor', 'desc')->get();

                    foreach ($item->calificaciones as &$calificacion) {
                        
                        $calificacion->valor = number_format($calificacion->valor, 2);

                    }

                    $item->detalle_evaluacion = DetalleEvaluacion::where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->first();

                    //return response()->json([$item, $request->id_evaluacion]);

                    $item->calificacion = number_format($item->detalle_evaluacion->calificacion / $item->valor, 2);

                }else{

                    // Validar si se obtiene la calificación desde otra función

                    $item->detalle_evaluacion = DetalleEvaluacion::where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->first();

                    if ($item->funcion_calculo) {
                        
                        $data = [
                            "usuario" => $colaborador->usuario,
                            "usuario2" => $colaborador->usuario_2,
                            "nit" => $colaborador->nit,
                            "month" => $evaluacion->mes,
                            "codarea" => $colaborador->codarea,
                            "id_item" => $item->id,
                            "id_evaluacion" => $request->id_evaluacion
                        ];

                        $result = app("App\Http\Controllers" . $item->controlador)->{$item->funcion_calculo}($data);

                        $item->calificacion = $result["calificacion"];
                        $item->editable = $result["editable"];
                        $item->info_calculo = $result["info_calculo"];
                        $item->motivos = $result["motivos"];  
                        $item->data_calculo = array_key_exists('data_calculo', $result) ? $result["data_calculo"] : null;  
                        
                    }else{

                        $valor_item = 0;

                        // Especificar cual será el valor
                        if ($area->iso == '1') {
                            
                            // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                            //$valor_item = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                            $valor_item = round($criterio->valor / count($items), 2);

                        }else{

                            // Si no es ISO asignar 
                            //$valor_item = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

                            $valor_item = round($criterio->valor_no_iso / count($items), 2);

                        }

                        if ($item->detalle_evaluacion) {
                            
                            $item->calificacion = round(($item->detalle_evaluacion->calificacion / $valor_item) * 100, 0);

                        }else{

                            $item->calificacion = 100;

                        }
                        

                        $item->editable = true;
                        $item->edit = true;

                    }

                    if ($item->detalle_evaluacion) {

                        $item->comentario = $item->detalle_evaluacion->comentario;

                    }else{

                        $item->comentario = null;

                    }
                    

                }

                /* 
                    Para obtener el valor de cada ítem dividir el
                    número de ítems entre el valor del criterio 
                    dependiendo si es ISO o NO ISO
                */

                // Especificar cual será el valor
                if ($area->iso == '1') {
                    
                    // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                    //$item->valor = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                    $item->valor = round($criterio->valor / count($items), 2);

                }else{

                    // Si no es ISO asignar 
                    //$item->valor = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

                    $item->valor = round($criterio->valor_no_iso / count($items), 2);

                }

                $item->check = false;
                $item->show_description = false;

            }

            $data = [
                "criterio" => $criterio,
                "items" => $items,
                "evaluacion" => $evaluacion,
                "detalle_colaborador" => [
                    "iso" => $area->iso,
                    "asesor" => $colaborador->jefe
                ]
            ];

            return response()->json($data);

        }

    }

?>