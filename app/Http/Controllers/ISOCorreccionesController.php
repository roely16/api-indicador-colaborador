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

    class ISOCorreccionesController extends Controller{
        
        public function c_nomenclatura($data){
            
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
                                            AND TIPO IS NULL");

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
            ];

            return $data;

        }

        public function c_sima($data){

            $usuario2 = $data["usuario2"];
            $usuario = $data["usuario"];

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
                                            AND (CB.USER_APLIC = '$usuario' OR CB.USER_APLIC = '$usuario2')");

            /* Obtener las correcciones */

            $correcciones = app('db')
                            ->connection('catastrousr')
                            ->select("  SELECT 
                                            *
                                        FROM CATASTRO.CDO_Q_DOCUMENTO
                                        WHERE ERROR = 1
                                        AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                        AND (USUARIO = '$usuario' OR USUARIO = '$usuario2')
                                        AND TIPO IS NULL");

            $motivos = [];
            $i = 0;

            foreach ($correcciones as $correccion) {
                
                $i++;
                
                $descrip = ["descripcion" => "Corrección No. " . $correccion->documento . '-' . $correccion->anio];

                $motivos [] = $descrip;

            }

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $i,
                "motivos" => $motivos
            ];

            return $data;

        }

        public function c_avisos_notariales($data){

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

            $operados = $result ? $result[0]->cantidad : 0;

            /* Obtener las correcciones */

            $correcciones = app('db')
                                ->connection('portales')
                                ->select("  SELECT 
                                                COUNT(*) AS CANTIDAD
                                            FROM ISO_AVISOS
                                            WHERE HISTORIAL LIKE '%$usuario%'
                                            AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                            AND RESULTADO = 'REVISION'
                                            AND TIPO IS NULL");

            $correcciones = $correcciones ? $correcciones[0]->cantidad : 0;

            $motivos = [
                [
                    "descripcion" => "Operados " . $operados
                ],
                [
                    "descripcion" => "Correcciones " . $correcciones
                ]
            ];

            $data= [
                "operados" => $operados,
                "correcciones" => $correcciones,
                "motivos" => $motivos, 
                "usuario" => $usuario
            ];

            return $data;

        }

        public function c_atencion($data){

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
                                            AND TIPO IS NULL");

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
            ];

            return $data;
        }

        public function c_cuenta_corriente($data){

            $usuario2 = $data["usuario2"];
            $usuario = $data["usuario"];

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
                                            AND (CB.USER_APLIC = '$usuario' OR CB.USER_APLIC = '$usuario2')");

            /* Obtener las correcciones */

            $correcciones = app('db')
                            ->connection('catastrousr')
                            ->select("  SELECT 
                                            *
                                        FROM CATASTRO.CDO_CALIDAD_CC
                                        WHERE ERROR = 'S'
                                        AND TO_CHAR(FECHA, 'YYYY-MM') = '$month'
                                        AND (USUARIO = '$usuario' OR USUARIO = '$usuario2')
                                        AND TIPO IS NULL");

            $motivos = [];
            $i = 0;

            foreach ($correcciones as $correccion) {
                
                $i++;
                
                $descrip = ["descripcion" => "Corrección No. " . $correccion->documento . '-' . $correccion->anio];

                $motivos [] = $descrip;

            }

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $i,
                "motivos" => $motivos
            ];

            return $data;

        }

        public function c_convenios($data){

            $usuario = $data["usuario"];
            $month = $data["month"];
            $correcciones = 0;

            $empleado = Empleado::where('usuario', $usuario)->first();

            $personas = app('db')
                        ->connection('cobros')
                        ->select("  SELECT  
                                        a.idpersona
                                    FROM  mco_persona a, mco_bitacora b
                                    WHERE  a.idpersona = b.idpersona
                                        AND b.idestado = 7
                                        AND TO_CHAR(a.fecha,'YYYY-MM') = '$month'
                                        AND a.idindicador = 1");
            
            foreach ($personas as $persona) {
                
                $bitacora = app('db')
                            ->connection('cobros')
                            ->select("  SELECT  *
                                            FROM  mco_bitacora
                                        WHERE  idpersona = '$persona->idpersona' 
                                        AND idestado = 8
                                        AND nit = '$empleado->nit'");

                if (count($bitacora) > 1) {
                    
                    $correcciones++;

                }

            }

            $bitacora = app('db')->connection('cobros')->select("   SELECT *
                                                                        FROM mco_bitacora
                                                                    WHERE idestado = 7
                                                                    AND nit = '$empleado->nit'
                                                                    AND idpersona IN (
                                                                        SELECT idpersona
                                                                        FROM mco_bitacora
                                                                        WHERE idestado = 8
                                                                                AND TO_CHAR(fecha,'YYYY-MM') = '$month'
                                                                        GROUP BY idpersona 
                                                                    )");

            $motivos = [];
            $i = 0;

            foreach ($bitacora as $item) {
                
                $i++;
                
                $descrip = ["descripcion" => "Corrección No. " . $item->idbitacora . "\r\n"];

                $motivos [] = $descrip;

            }

            $data= [
                "operados" => 10,
                "correcciones" => count($bitacora),
                "motivos" => $motivos
            ];

            return $data;

        }

        public function c_liquidaciones($data){

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

            foreach ($operados as $item) {
                
                if ($item->zestado == 'F') {
                    
                    $i++;

                    $motivos [] = ["descripcion" => $item->zcaso . "\r\n"];

                }

            }

            $data= [
                "operados" => count($operados),
                "correcciones" => $i,
                "motivos" => $motivos
            ];

            return $data;

        }

    }

?>