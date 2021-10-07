<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;
    use App\Ponderacion;
    use App\Empleado;
    use App\Area;
    use App\Evaluacion;

    class CincoSController extends Controller{

        public function observaciones_recorridos($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fuente = 2;

            // Obtener todas las observaciones

            $observaciones = app('db')->select("     SELECT
                                                        CORRELATIVO AS DESCRIPCION
                                                    FROM CERT_MAESTRO
                                                    WHERE ID_METODOLOGIA = 1
                                                    AND TO_CHAR(FECHA_OBSERVACION, 'YYYY-MM') = '$month'
                                                    AND ESTADO = 'A'
                                                    AND ID_FUENTE = $id_fuente
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
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de 5S's.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function observaciones_auditorias($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fuente = 1;

             // Obtener las observaciones

             $observaciones = app('db')->select("SELECT 
                                                    CORRELATIVO AS DESCRIPCION
                                                FROM OBSERVACIONES_5S
                                                WHERE NIT = '$nit'
                                                AND FUENTE = 'AUDITORIA'
                                                AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");
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
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de 5S's.",
                "motivos" => $observaciones
            ];

            return $data;

        }

        public function observaciones_colaboradores($data){

            $nit = $data["nit"];
            $month = $data["month"];
            $id_fuente = 3;

            // Obtener las observaciones

            $observaciones = app('db')->select("SELECT 
                                                    CORRELATIVO AS DESCRIPCION
                                                FROM OBSERVACIONES_5S
                                                WHERE NIT = '$nit'
                                                AND FUENTE = 'COLABORADOR'
                                                AND TO_CHAR(FECHA_OBS, 'YYYY-MM') = '$month'");
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
                "info_calculo" => "Cálculo realizado en base a la información obtenida del módulo de 5S's.",
                "motivos" => $observaciones
            ];

            return $data;

        }

    }

?>