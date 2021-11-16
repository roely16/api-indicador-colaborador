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

    class ISONoConformesController extends Controller{
        
        public function s_nomenclatura($data){

            $usuario = $data["usuario2"];
            $month = $data["month"];
            $codarea = $data["codarea"];

            /* Obtener los operados */

            $result = app('db')
                        ->connection('catastrousr')
                        ->select("  SELECT  
                                            COUNT(*) AS CANTIDAD
                                    FROM  CDO_DOCUMENTO CD,
                                            CDO_BANDEJA CB
                                    WHERE  CD.ANIO = CB.ANIO
                                            AND CD.DOCUMENTO = CB.DOCUMENTO
                                            AND CD.CODIGOCLASE = CB.CODIGOCLASE
                                            AND CB.STATUS_TAREA = 5
                                            AND CB.DEPENDENCIA = 90
                                            AND TO_CHAR(CD.FECHA,'YYYY-MM') = '$month'
                                            AND CB.USER_APLIC = '$usuario'");

            /* Obtener las correcciones */

            $correcciones = app('db')
                                ->connection('portales')
                                ->select("  SELECT 
                                                COUNT(*) AS CORRECCIONES
                                            FROM ISO_NOMENCLATURA
                                            WHERE HISTORIAL LIKE '%$usuario%'
                                            AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                            AND RESULTADO = 'RECHAZADO'
                                            AND TIPO = 'SNC'");

            /* Obtener los servicios no conformes del nuevo módulo */
            $snc = app('db')->connection('catastrousr')->select("   SELECT 
                                                                        *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as $item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }            

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_sima($data){

            $usuario = $data["usuario2"];
            $month = $data["month"];

            /* Obtener los operados */

            $result = app('db')
                        ->connection('catastrousr')
                        ->select("  SELECT  
                                            COUNT(*) AS CANTIDAD
                                    FROM  CDO_DOCUMENTO CD,
                                            CDO_BANDEJA CB
                                    WHERE  CD.ANIO = CB.ANIO
                                            AND CD.DOCUMENTO = CB.DOCUMENTO
                                            AND CD.CODIGOCLASE = CB.CODIGOCLASE
                                            AND CB.STATUS_TAREA = 5
                                            AND CB.DEPENDENCIA = 94
                                            AND TO_CHAR(CD.FECHA,'YYYY-MM') = '$month'
                                            AND CB.USER_APLIC = '$usuario'");

            /* Obtener las correcciones */

            $correcciones = app('db')
                            ->connection('catastrousr')
                            ->select("  SELECT 
                                            COUNT(*) AS CORRECCIONES
                                        FROM CATASTRO.CDO_Q_DOCUMENTO
                                        WHERE ERROR = 1
                                        AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                        AND USUARIO = '$usuario'
                                        AND TIPO = 'SNC'");

            /* Obtener los servicios no conformes del nuevo módulo */
            $snc = app('db')->connection('catastrousr')->select("   SELECT 
                                                                        *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as &$item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }      

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_avisos_notariales($data){

            $usuario = $data["usuario2"];
            $month = $data["month"];

            /* Obtener los operados */

            $result = app('db')
                        ->connection('catastrousr')
                        ->select("  SELECT  
                                            COUNT(*) AS CANTIDAD
                                    FROM  CDO_DOCUMENTO CD,
                                            CDO_BANDEJA CB
                                    WHERE  CD.ANIO = CB.ANIO
                                            AND CD.DOCUMENTO = CB.DOCUMENTO
                                            AND CD.CODIGOCLASE = CB.CODIGOCLASE
                                            AND CB.STATUS_TAREA = 5
                                            AND CB.DEPENDENCIA = 18
                                            AND TO_CHAR(CD.FECHA,'YYYY-MM') = '$month'
                                            AND CB.USER_APLIC = '$usuario'");

            /* Obtener las correcciones */

            $correcciones = app('db')
                                ->connection('portales')
                                ->select("  SELECT 
                                                COUNT(*) AS CORRECCIONES
                                            FROM ISO_AVISOS
                                            WHERE HISTORIAL LIKE '%$usuario%'
                                            AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                            AND RESULTADO = 'RECHAZADO'
                                            AND TIPO = 'SNC'");

            /* Obtener los servicios no conformes del nuevo módulo */
            $snc = app('db')->connection('catastrousr')->select("   SELECT 
                                                                        *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as $item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }      

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_atencion($data){

            // ID del Proceo = 15

            $usuario = $data["usuario2"];
            $month = $data["month"];

            /* Obtener los operados */

            $result = app('db')
                        ->connection('catastrousr')
                        ->select("  SELECT
                                        COUNT(*) AS CANTIDAD
                                    FROM  CATASTRO.AAV_INGRESO_EXPEDIENTE
                                    WHERE  TO_CHAR(FECHA,'YYYY-MM') = '$month'
                                    AND USUARIO = '$usuario'");

            /* Obtener las correcciones */

            $correcciones = app('db')
                                ->connection('portales')
                                ->select("  SELECT 
                                                COUNT(*) AS CORRECCIONES
                                            FROM ISO_ATENCION_USUARIO
                                            WHERE USUARIO_TRABAJO LIKE '%$usuario%'
                                            AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                            AND RESULTADO = 'INCOMPLETO'
                                            AND TIPO = 'SNC'");

            /* Obtener los servicios no conformes del nuevo módulo */
            $snc = app('db')->connection('catastrousr')->select("   SELECT 
                                                                        *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as $item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }            

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_cuenta_corriente($data){

            $usuario = $data["usuario2"];
            $month = $data["month"];

            /* Obtener los operados */

            $result = app('db')
                        ->connection('catastrousr')
                        ->select("  SELECT  
                                            COUNT(*) AS CANTIDAD
                                    FROM  CDO_DOCUMENTO CD,
                                            CDO_BANDEJA CB
                                    WHERE  CD.ANIO = CB.ANIO
                                            AND CD.DOCUMENTO = CB.DOCUMENTO
                                            AND CD.CODIGOCLASE = CB.CODIGOCLASE
                                            AND CB.STATUS_TAREA = 5
                                            AND CB.DEPENDENCIA = 30
                                            AND TO_CHAR(CD.FECHA,'YYYY-MM') = '$month'
                                            AND CB.USER_APLIC = '$usuario'");

            /* Obtener las correcciones */

            $correcciones = app('db')
                            ->connection('catastrousr')
                            ->select("  SELECT 
                                            COUNT(*) AS CORRECCIONES
                                        FROM CATASTRO.CDO_CALIDAD_CC
                                        WHERE ERROR = 'S'
                                        AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                        AND USUARIO = '$usuario'
                                        AND TIPO = 'SNC'");

            /* Obtener los servicios no conformes del nuevo módulo */
            $snc = app('db')->connection('catastrousr')->select("   SELECT 
                                                                        *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as $item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }            

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_convenios($data){
            
            $usuario = $data["usuario2"];
            $month = $data["month"];
            $nit = $data["nit"];

            // Obtener el nit de la persona
            $operados = app('db')->connection('cobros')->select("   SELECT COUNT(*) AS TOTAL
                                                                    FROM MCO_BITACORA
                                                                    WHERE TO_CHAR(FECHA,'YYYY-MM') = '$month'
                                                                    AND IDESTADO = 7
                                                                    AND NIT = '$nit'");

            $snc = app('db')->connection('catastrousr')->select("   SELECT *
                                                                    FROM SNC_CONTROL
                                                                    WHERE USUARIO = UPPER('$usuario')
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            foreach ($snc as $item) {
                $item->descripcion = "Servicio No Conforme " . $item->documento . '-' . $item->anio;
                $total++;
            }            

            $data= [
                "operados" => $operados ? $operados[0]->total : 0,
                "snc" => $total,
                "motivos" => $snc
            ];

            return $data;

        }

        public function s_liquidaciones($data){

            $usuario = $data["usuario"];
            $usuario2 = $data["usuario2"];
            $month = $data["month"];

            $start = date('Ym01', strtotime($month));
            $end = date('Ymt', strtotime($month));

            $i = 0;
            $motivos = [];

            $operados = app('db')
                            ->connection('portales')
                            ->select("  SELECT *
                                            FROM ISO_SAP_RFC_LIQUIDACIONES
                                        WHERE ZFECHA_EXTRACCION BETWEEN $start AND $end
                                        AND ZUSUARIO_EXTRACCION = '$usuario2'");


            $snc = app('db')->connection('catastrousr')->select("   SELECT *
                                                                    FROM SNC_CONTROL
                                                                    WHERE (USUARIO = UPPER('$usuario') OR USUARIO = UPPER('$usuario2'))
                                                                    AND TO_CHAR(FECHA_DOCUMENTO, 'YYYY-MM') = '$month'");

            $total = 0;

            $motivos = [];
            foreach ($snc as $item) {

                $motivos [] = ["descripcion" => "Servicio No Conforme " . $item->documento . '-' . $item->anio];

                $total++;

            }        

            $data= [
                "operados" => count($operados),
                "snc" => $total,
                "motivos" => $motivos
            ];

            return $data;

        }

    }

?>