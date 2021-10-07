<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;
    use App\Ponderacion;
    use App\Empleado;
    use App\Area;
    use App\Evaluacion;

    class OficinaVerdeController extends Controller{

        public function cumplimiento_practicas($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fase = 6;

            // Obtener todas las observaciones

            $observaciones = app('db')->select("     SELECT
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM CERT_MAESTRO
                                                    WHERE ID_METODOLOGIA = 2
                                                    AND TO_CHAR(FECHA_OBSERVACION, 'YYYY-MM') = '$month'
                                                    AND ESTADO = 'A'
                                                    AND ID_FASE = $id_fase
                                                    AND NIT = '$nit'");

            $total = 0;

            foreach ($observaciones as &$observacion) {

                $observacion->descripcion = "Observación No. " . $observacion->descripcion;
                $total++;
                
            }

            $calificacion = 100;

            if ($total > 0) {
                
                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de Oficina Verde.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function traslado_residuos($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fase = 7;

            // Obtener todas las observaciones

            $observaciones = app('db')->select("     SELECT
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM CERT_MAESTRO
                                                    WHERE ID_METODOLOGIA = 2
                                                    AND TO_CHAR(FECHA_OBSERVACION, 'YYYY-MM') = '$month'
                                                    AND ESTADO = 'A'
                                                    AND ID_FASE = $id_fase
                                                    AND NIT = '$nit'");

            $total = 0;

            foreach ($observaciones as &$observacion) {

                $observacion->descripcion = "Observación No. " . $observacion->descripcion;
                $total++;
                
            }

            $calificacion = 100;

            if ($total > 0) {
                
                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de Oficina Verde.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function cuestionarios($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fase = 9;

            // Obtener todas las observaciones

            $observaciones = app('db')->select("     SELECT
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM CERT_MAESTRO
                                                    WHERE ID_METODOLOGIA = 2
                                                    AND TO_CHAR(FECHA_OBSERVACION, 'YYYY-MM') = '$month'
                                                    AND ESTADO = 'A'
                                                    AND ID_FASE = $id_fase
                                                    AND NIT = '$nit'");

            $total = 0;

            foreach ($observaciones as &$observacion) {

                $observacion->descripcion = "Observación No. " . $observacion->descripcion;
                $total++;
                
            }

            $calificacion = 100;

            if ($total > 0) {
                
                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de Oficina Verde.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function asistencia_capacitaciones($data){
            
            $nit = $data["nit"];
            $month = $data["month"];
            $id_fase = 8;

            // Obtener todas las observaciones

            $observaciones = app('db')->select("     SELECT
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM CERT_MAESTRO
                                                    WHERE ID_METODOLOGIA = 2
                                                    AND TO_CHAR(FECHA_OBSERVACION, 'YYYY-MM') = '$month'
                                                    AND ESTADO = 'A'
                                                    AND ID_FASE = $id_fase
                                                    AND NIT = '$nit'");

            $total = 0;

            foreach ($observaciones as &$observacion) {

                $observacion->descripcion = "Observación No. " . $observacion->descripcion;
                $total++;
                
            }

            $calificacion = 100;

            if ($total > 0) {
                
                $restar = $total * 25;

                if ($restar <= 100) {
                    
                    $calificacion = $calificacion - $restar;

                }else{

                    $calificacion = 0;

                }

            }

            $data = [
                "calificacion" => $calificacion,
                "editable" => false,
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de Oficina Verde.",
                "motivos" => $observaciones
            ];

            return $data;

        }

    }

?>