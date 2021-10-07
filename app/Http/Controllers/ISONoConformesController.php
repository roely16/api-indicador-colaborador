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

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
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

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
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

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
            ];

            return $data;

        }

        public function s_atencion($data){

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

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
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

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "snc" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
            ];

            return $data;

        }

        public function s_convenios($data){
            
        }

    }

?>