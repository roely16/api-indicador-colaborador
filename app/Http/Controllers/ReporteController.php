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
                
                //$items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_asesor', 'S')->get();

                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_ASESOR = 'S'
                                                ORDER BY T1.ID ASC");

            }else{

                //$items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_prestador', 'S')->get();

                $items = app('db')->select("    SELECT *
                                                FROM RRHH_IND_CRITERIO_ITEM T1
                                                INNER JOIN RRHH_IND_CRITERIO_ITEM_AREA T2
                                                ON T1.ID = T2.ID_ITEM
                                                WHERE T2.CODAREA = $colaborador->codarea
                                                AND T1.ID_CRITERIO = $criterio->id
                                                AND T1.APLICA_PRESTADOR = 'S'
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
                            "nit" => $colaborador->nit,
                            "month" => $request->month
                        ];

                        $result = $this->{$item->funcion_calculo}($data);

                        $item->calificacion = $result["calificacion"];
                        $item->editable = $result["editable"];
                        $item->info_calculo = $result["info_calculo"];
                        $item->motivos = $result["motivos"];                        

                    }else{

                        $item->calificacion = 100;
                        $item->edit = true;
                        $item->editable = true;

                    }

                    $item->comentario = null;

                }

                // Especificar cual será el valor
                if ($area->iso == '1') {
                    
                    // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                    $item->valor = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                }else{

                    // Si no es ISO asignar 
                    $item->valor = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

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
                
                $items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_asesor', 'S')->get();

            }else{

                $items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_prestador', 'S')->get();

            }

            foreach ($items as $item) {
                
                // Si el metodo de evaluación es ponderación
                if ($criterio->metodo_calificacion == 'ponderacion') {
                    
                    $item->calificaciones = Ponderacion::where('id_criterio_item', $item->id)->orderBy('valor', 'desc')->get();

                    foreach ($item->calificaciones as &$calificacion) {
                        
                        $calificacion->valor = number_format($calificacion->valor, 2);

                    }

                    $item->detalle_evaluacion = DetalleEvaluacion::where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->first();

                    $item->calificacion = number_format($item->detalle_evaluacion->calificacion / $item->valor, 2);

                }else{

                    // Validar si se obtiene la calificación desde otra función

                    $item->detalle_evaluacion = DetalleEvaluacion::where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->first();

                    if ($item->funcion_calculo) {
                        
                        $data = [
                            "usuario" => $colaborador->usuario,
                            "nit" => $colaborador->nit,
                            "month" => $evaluacion->mes
                        ];

                        $result = $this->{$item->funcion_calculo}($data);

                        $item->calificacion = $result["calificacion"];
                        $item->editable = $result["editable"];
                        $item->info_calculo = $result["info_calculo"];
                        $item->motivos = $result["motivos"];  
                        
                    }else{

                        $valor_item = 0;

                        // Especificar cual será el valor
                        if ($area->iso == '1') {
                            
                            // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                            $valor_item = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                        }else{

                            // Si no es ISO asignar 
                            $valor_item = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

                        }

                        $item->calificacion = round(($item->detalle_evaluacion->calificacion / $valor_item) * 100, 0);

                        $item->editable = true;
                        $item->edit = true;

                    }

                    $item->comentario = $item->detalle_evaluacion->comentario;

                }

                // Especificar cual será el valor
                if ($area->iso == '1') {
                    
                    // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                    $item->valor = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                }else{

                    // Si no es ISO asignar 
                    $item->valor = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

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

        public function quejas($data){

            $usuario = $data["usuario"];
            $month = $data["month"];

            $quejas = app('db')->select("   SELECT COUNT(*) AS TOTAL
                                            FROM SQ_QUEJA
                                            WHERE DIRIGIDO_A = '$usuario'
                                            AND CLASIFICACION = 'QUEJA'
                                            AND TO_CHAR(FECHA_QUEJA, 'YYYY-MM') = '$month'");

            $total = $quejas[0]->total;

            $calificacion = 100;

            if ($total >= 1 && $total <= 3) {
                
                $calificacion = 90;

            }elseif($total >= 4 && $total <= 7){

                $calificacion = 75;

            }elseif($total >= 8 && $total <= 10){

                $calificacion = 25;

            }elseif($total > 10){

                $calificacion = 0;

            }

            $motivos = [];

            // Obtener información de la queja 
            if($total > 0){

                $motivos = app('db')->select("  SELECT 
                                                    CORREL_QUEJA AS DESCRIPCION
                                                FROM SQ_QUEJA
                                                WHERE DIRIGIDO_A = '$usuario'
                                                AND CLASIFICACION = 'QUEJA'
                                                AND TO_CHAR(FECHA_QUEJA, 'YYYY-MM') = '$month'");

                foreach ($motivos as &$motivo) {
                    
                    $motivo->descripcion = "Queja No. " . $motivo->descripcion;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de quejas.",
                "motivos" => $motivos,
            ];

            return $data;

        }

        public function observaciones_recorridos($data){

            $nit = $data["nit"];
            $month = $data["month"];

            $result = app('db')->select("   SELECT 
                                                COUNT(*) AS TOTAL
                                            FROM OBSERVACIONES_5S
                                            WHERE NIT = '$nit'
                                            AND FUENTE = 'RECORRIDO'
                                            AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");

            $calificacion = 100;

            if ($result) {
                
                $total = $result[0]->total;

                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

                // Obtener los correlativos de las observaciones

                $observaciones = app('db')->select("SELECT 
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM OBSERVACIONES_5S
                                                    WHERE NIT = '$nit'
                                                    AND FUENTE = 'RECORRIDO'
                                                    AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");

                foreach ($observaciones as &$observacion) {
                                    
                    $observacion->descripcion = "Observación No. " . $observacion->descripcion;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de 5S's.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function observaciones_auditorias($data){

            $nit = $data["nit"];
            $month = $data["month"];

            $result = app('db')->select("   SELECT 
                                                COUNT(*) AS TOTAL
                                            FROM OBSERVACIONES_5S
                                            WHERE NIT = '$nit'
                                            AND FUENTE = 'AUDITORIA'
                                            AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");

            $calificacion = 100;

            if ($result) {
                
                $total = $result[0]->total;

                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

                // Obtener las observaciones

                $observaciones = app('db')->select("SELECT 
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM OBSERVACIONES_5S
                                                    WHERE NIT = '$nit'
                                                    AND FUENTE = 'AUDITORIA'
                                                    AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");

                foreach ($observaciones as &$observacion) {
                    
                    $observacion->descripcion = "Observación No. " . $observacion->descripcion;

                }
            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de 5S's.",
                "motivos" => $observaciones
            ];

            return $data;

        }

    }

?>