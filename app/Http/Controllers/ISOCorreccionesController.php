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
                                        AND TIPO IS NULL");

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
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

            $usuario = $data["usuario2"] ? $data["usuario2"] : $data["usuario"];
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
                                        AND TIPO IS NULL");

            $data= [
                "operados" => $result ? $result[0]->cantidad : 0,
                "correcciones" => $correcciones ? $correcciones[0]->correcciones : 0,
                "motivos" => []
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
                            ->select("  SELECT  COUNT(*) AS calidad
                                            FROM  mco_bitacora
                                        WHERE  idpersona = '$persona->idpersona' 
                                        AND idestado = 8
                                        AND nit = '$empleado->nit'");

                if (count($bitacora) > 1) {
                    
                    $correcciones++;

                }

            }

            $data= [
                "operados" => 10,
                "correcciones" => $correcciones,
                "motivos" => []
            ];

            return $data;

        }

    }

?>