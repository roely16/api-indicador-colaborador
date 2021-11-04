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

    class ISOController extends Controller{

        public function correcciones($data){

            $codarea = $data["codarea"];
            $id_item = $data["id_item"];

            /* Buscar la configuración del cálculo del item */
            $result = app('db')->select("   SELECT *
                                            FROM RRHH_IND_ITEM_AREA
                                            WHERE CODAREA = '$codarea'
                                            AND ID_ITEM = '$id_item'");

            if ($result) {
                
                $result = $result[0];

                if ($result->func_calculo) {
                    
                    $datos = app("App\Http\Controllers" . $result->controlador)->{$result->func_calculo}($data);

                    $datos["show_correcciones"] = true;
                    $datos["editable"] = false;

                }else{

                    // Si existe un id_evaluación obtener el detalle
                    if (array_key_exists('id_evaluacion', $data)) {
                        
                        $id_evaluacion = $data["id_evaluacion"];

                        // Buscar el detalle del item y de la evaluación
                        $detalle_evaluacion = app('db')->select("   SELECT 
                                                                        OPERADOS, 
                                                                        CORRECCIONES
                                                                    FROM RRHH_IND_EVALUACION_DETALLE
                                                                    WHERE ID_EVALUACION = $id_evaluacion
                                                                    AND ID_ITEM = $id_item");

                        if ($detalle_evaluacion) {
                            
                            $detalle_evaluacion = $detalle_evaluacion[0];

                        }
                    }else{

                        $detalle_evaluacion = null;

                    }

                    $datos = [
                        "operados" => $detalle_evaluacion ?  $detalle_evaluacion->operados : null,
                        "correcciones" => $detalle_evaluacion ?  $detalle_evaluacion->correcciones : null,
                        "editable" => true,
                        "show_correcciones" => true
                    ];

                }

            }

            $datos = (object) $datos;

            $porcentaje_resta = 0;

            if ($result->func_calculo && $datos->operados) {
                
                $porcentaje_resta = round(100 * ($datos->correcciones / $datos->operados), 2);

            }else{

                if (array_key_exists('id_evaluacion', $data)) {

                    //$porcentaje_resta = round(100 * ($datos->correcciones / $datos->operados), 2);

                    $porcentaje_resta = 0;

                }

            }

            $data = [
                "calificacion" => $result->func_calculo ? 100 - $porcentaje_resta : 100 - $porcentaje_resta,
                "editable" => false,
                "info_calculo" => "Cálculo realizado automáticamente.",
                "motivos" => $result->func_calculo ? $datos->motivos : [],
                "data_calculo" => $datos
            ];

            return $data;
        }

        public function servicios_no_conformes($data){

            $codarea = $data["codarea"];
            $id_item = $data["id_item"];

            /* Buscar la configuración del cálculo del item */
            $result = app('db')->select("   SELECT *
                                            FROM RRHH_IND_ITEM_AREA
                                            WHERE CODAREA = '$codarea'
                                            AND ID_ITEM = '$id_item'");

            if ($result) {
                
                $result = $result[0];

                if ($result->func_calculo) {
                    
                    $datos = app("App\Http\Controllers" . $result->controlador)->{$result->func_calculo}($data);

                    $datos["show_snc"] = true;
                    $datos["editable"] = false;

                }else{
                    
                     // Si existe un id_evaluación obtener el detalle
                     if (array_key_exists('id_evaluacion', $data)) {
                        
                        $id_evaluacion = $data["id_evaluacion"];

                        // Buscar el detalle del item y de la evaluación
                        $detalle_evaluacion = app('db')->select("   SELECT 
                                                                        OPERADOS, 
                                                                        SNC
                                                                    FROM RRHH_IND_EVALUACION_DETALLE
                                                                    WHERE ID_EVALUACION = $id_evaluacion
                                                                    AND ID_ITEM = $id_item");

                        if ($detalle_evaluacion) {
                            
                            $detalle_evaluacion = $detalle_evaluacion[0];

                        }

                    }else{

                        $detalle_evaluacion = null;

                    }

                    $datos = [
                        "operados" => $detalle_evaluacion ?  $detalle_evaluacion->operados : null,
                        "snc" => $detalle_evaluacion ?  $detalle_evaluacion->snc : null,
                        "editable" => true,
                        "show_snc" => true
                    ];

                }

            }

            $datos = (object) $datos;

            $porcentaje_resta = 0;

            if ($result->func_calculo && $datos->operados) {
                
                $porcentaje_resta = round(100 * ($datos->snc / $datos->operados), 2);

            }else{

                if (array_key_exists('id_evaluacion', $data)) {

                    //$porcentaje_resta = round(100 * ($datos->snc / $datos->operados), 2);

                    $porcentaje_resta = 0;

                }

            }

            $data = [
                "calificacion" => $result->func_calculo ? 100 - $porcentaje_resta : 100 - $porcentaje_resta,
                "editable" => false,
                "info_calculo" => "Cálculo realizado automáticamente.",
                "motivos" => [],
                "data_calculo" => $datos
            ];

            return $data;

        }

        public function quejas($data){

            $usuario = $data["usuario"];
            $month = $data["month"];

            $quejas = app('db')->select("   SELECT COUNT(*) AS TOTAL
                                            FROM SQ_QUEJA
                                            WHERE DIRIGIDO_A = '$usuario'
                                            AND CLASIFICACION = 'QUEJA'
                                            AND TO_CHAR(FECHA_ACUSE_RECIBO, 'YYYY-MM') = '$month'");

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
                                                AND TO_CHAR(FECHA_ACUSE_RECIBO, 'YYYY-MM') = '$month'");

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

        public function encuestas_cliente_interno($data){

            $nit = $data["nit"];
            $month = $data["month"];

            $empleado = Empleado::where('nit', $nit)->first();

            $pendientes = 0;
            $motivos = [];

            /*
                Validar si existen encuestas de informatica pendientes
            */

            $informatica = app('db')->select("  SELECT 
                                                    COUNT(*) AS TOTAL
                                                FROM MSA_MEDICION_ENCABEZADO
                                                WHERE CONTACTO = '$empleado->emailmuni'
                                                AND ID_MEDICION = 6
                                                AND MEDICION_REALIZADA IS NULL
                                                AND TO_CHAR(FECHA_CARGA, 'YYYY-MM') = '$month'");


            if ($informatica) {
                                
                if (intval($informatica[0]->total)) {
                    
                    $pendientes += intval($informatica[0]->total);

                    $motivos [] = [
                        "descripcion" => 'Encuestas pendientes de informática: ' . $informatica[0]->total
                    ];

                }         

            }

            /*
                Validar si existen encuestas de vales pendientes
            */

            $vales = app('db')->select("    SELECT 
                                                COUNT(*) AS TOTAL
                                            FROM MSA_MEDICION_ENCABEZADO
                                            WHERE ID_MEDICION = 7
                                            AND MEDICION_REALIZADA IS NULL
                                            AND CLIENTE = '$empleado->usuario'
                                            AND TO_CHAR(FECHA_CARGA, 'YYYY-MM') = '$month'");

            if ($vales) {
                                            
                if (intval($vales[0]->total) > 0) {
                    
                    $pendientes += intval($vales[0]->total);

                    $motivos [] = [
                        "descripcion" => 'Encuestas pendientes de vales: ' . $vales[0]->total
                    ];

                }     

            }

            // if (count($motivos) <= 0) {
                
            //     $motivos = [];

            // }

            $response = [
                "calificacion" => 100 - (5 * $pendientes),
                "editable" => false,
                "info_calculo" => "Cálculo realizado automáticamente.",
                "motivos" => $motivos,
            ];

            return $response;

        }

    }

?>