<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Area;
    use App\Empleado;
    use App\Criterio;
    use App\DetalleEvaluacion;

    use App\Exports\DashboardExport;
    use Maatwebsite\Excel\Facades\Excel;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;

    use App\Jobs\EvaluacionJob;

    class DashboardController extends Controller{

        public function dashboard_area(Request $request){

            /* 
                PERSONAS CON NOTAS POR DEFECTO
            */
            
            $exclusiones = [
                [
                    "nombre" => "Ing. Maynor Cárcamo",
                    "nit" => "838152-6",
                    "criterios" => [1, 5, 8]
                ]
            ];

            $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            // Mes actual
            $month = $request->month;
            $areas = [];
            $codareas = [];

            if (is_array($request->codarea)) {
                
                $codareas = $request->codarea;

            }else{

                $codareas[] = $request->codarea;

            }

            foreach ($codareas as $codarea) {
                
                $area = Area::find($codarea);

                $empleados = Empleado::where('codarea', $codarea)->where('status', 'A')->get();

                // Obtener los criterios para el dashboard

                foreach ($empleados as &$empleado) {
                    
                    $month_array = explode("-", $month);

                    $empleado->mes = $meses[intval($month_array[1] - 1)];

                    // Obtener la imagen de cada colaborador
                    $imagen = app('db')->select("   SELECT *
                                                    FROM RH_RUTA_PDF
                                                    WHERE NIT = '$empleado->nit'
                                                    AND IDCAT = '11'");

                    if ($imagen) {

                        $empleado->imagen64 =  'http://172.23.25.31/GestionServicios/' . $imagen[0]->ruta;

                    }else{

                        $empleado->imagen = null;
                        $empleado->imagen64 = null;

                    }

                    $criterios = Criterio::all();

                    $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;
                    $empleado->criterios = $criterios;

                    // Por cada uno de los criterios 
                    foreach ($empleado->criterios as &$criterio) {

                        foreach ($exclusiones as $exclusion) {
                            
                            foreach ($exclusion["criterios"] as $criterio_ex) {
                                
                                if ($empleado->nit == $exclusion["nit"] && $criterio->id == $criterio_ex) {
                                    
                                    $criterio->calificacion = 100;
                                    $criterio->exclucion = true;
                                    $criterio->color = 'green';
                                    
                                    $empleado->total_mensual += round(($criterio->valor * $criterio->calificacion) / 100, 2);

                                }else{

                                    $empleado->exclucion = false;

                                }

                            }

                        }
                        
                        if (!$criterio->exclucion) {

                            /*
                                Validar si se trata de un proceso ISO o no
                            */

                            $area = Area::find($empleado->codarea);

                            if ($area->iso == '1') {
                                
                                $valor_criterio = $criterio->valor;

                            }else{

                                $valor_criterio = $criterio->valor_no_iso;

                            }

                            if (!$criterio->funcion_calculo) {
                                
                                // Buscar la última evaluación según el criterio
                                $evaluacion = app('db')->select("   SELECT *
                                                                    FROM RRHH_IND_EVALUACION
                                                                    WHERE ID_CRITERIO = $criterio->id
                                                                    AND ID_PERSONA = '$empleado->nit'
                                                                    AND MES = '$month'");

                                if ($evaluacion) {
                                    
                                    // Calcular la calificación

                                    $evaluacion = $evaluacion[0];

                                    $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                                    $total = 0;
                                    $str_motivo = null;

                                    foreach ($detalle as $item) {
                                        
                                        $total += $item->calificacion;

                                        if ($item->motivo) {
                                            
                                            $str_motivo = $str_motivo . $item->motivo;

                                        }

                                    }

                                    $criterio->motivo = $str_motivo;

                                    $criterio->calificacion = floor($evaluacion->calificacion);

                                    $criterio->calificacion = $criterio->calificacion > 100 ? 100 : $criterio->calificacion;

                                    if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                                    
                                        $criterio->color = 'red';

                                    }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){

                                        $criterio->color = 'orange';

                                    }else{

                                        $criterio->color = 'green';

                                    }

                                    $criterio->nota_individual = round(($valor_criterio * $criterio->calificacion) / 100, 2);
                                    $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);
                                    
                                }else{

                                    /*
                                        Si no tiene evaluación validar si es necesario asignarle una nota por defecto    
                                    */

                                    if ($criterio->calificacion_default) {
                                        
                                        $criterio->pendiente = false;
                                        $criterio->calificacion = 100;

                                        if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                                    
                                            $criterio->color = 'red';
            
                                        }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){
            
                                            $criterio->color = 'orange';
            
                                        }else{
            
                                            $criterio->color = 'green';
            
                                        }

                                        $criterio->nota_individual = round(($valor_criterio * $criterio->calificacion) / 100, 2);
                                        $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);

                                    }else{

                                        $criterio->pendiente = true;

                                    }
                                }

                            }else{

                                $data = [
                                    "colaborador" => $empleado,
                                    "criterio" => $criterio,
                                    "month" => $month,
                                ];

                                $result = $this->{$criterio->funcion_calculo}($data);

                                $criterio->color = $result["color"];
                                $criterio->calificacion = $result["calificacion"];
                                $criterio->pendiente = $result["pendiente"];
                                $criterio->motivo = $result["motivo"];


                                $criterio->nota_individual = round(($valor_criterio * $criterio->calificacion) / 100, 2);
                                $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);

                            }

                        }
                        
                    }

                    $empleado->stars = ($empleado->total_mensual * 5) / 100;
                    $empleado->loading_anual = true;

                }

                $area->empleados = $empleados;

                $areas [] = $area;

            }

            return response()->json($areas);

        }
        
        public function performance($data){

            $colaborador = $data["colaborador"];

            $arr_month = explode("-", $data["month"]);

            $year = $arr_month[0];

            /*
                Buscar si existe una temporada para el año seleccionado
            */

            $result = app('db')->select("   SELECT 
                                                TEMPORADAID
                                            FROM TEMPORADAS
                                            WHERE NOMBRE LIKE '%$year%'
                                            ORDER BY TEMPORADAID DESC");

            // Validar que existan registros para la temporada
            if ($result) {
                
                $tempid = $result[0]->temporadaid;

                $total_evaluaciones = app('db')->select("   SELECT COUNT(*) AS TOTAL
                                                            FROM RH_RESULTADOS_TEMPORADA
                                                            WHERE TEMPORADAID = $tempid
                                                            AND NIT = '$colaborador->nit'");
            }

            if (intval($total_evaluaciones[0]->total) > 0) {
                
                $temporadaid = $result[0]->temporadaid;

            }else{

                // Reducir 1 al año 
                $year = $year - 1;

                $result = app('db')->select("   SELECT 
                                                    TEMPORADAID
                                                FROM TEMPORADAS
                                                WHERE NOMBRE LIKE '%$year%'
                                                ORDER BY TEMPORADAID DESC");

                $temporadaid = $result[0]->temporadaid;

            }

            $colaborador->total_evaluaciones = $total_evaluaciones;

            $result_colaborador = Empleado::where('nit', $colaborador->nit)->first();

            $colaborador->jefe = $result_colaborador->jefe;

            // Obtener los porcentaje de evaluación
            $result = app('db')->select("   SELECT *
                                            FROM RH_COLABORADORES_TEMPORADA
                                            WHERE NIT = '$colaborador->nit'
                                            AND TEMPORADAID = $temporadaid");
            
            if ($result) {
                
                $colaborador->porcentaje_colega = $result[0]->porcentaje_colega;
                $colaborador->porcentaje_asesor = $result[0]->porcentaje_asesor;
                $colaborador->temporadaid = $temporadaid;
                $colaborador->pendiente = false;

            }else{

                $colaborador->pendiente = true;
            }

            // Por cada colaborador buscar la evaluación de superior
            $evaluacion_superior = app('db')->select("  SELECT *
                                                        FROM RH_RESULTADOS_TEMPORADA
                                                        WHERE TEMPORADAID = '$colaborador->temporadaid'
                                                        AND NIT = '$colaborador->nit'
                                                        AND TIPOEV = 'superior'");

            $colaborador->superior = $evaluacion_superior;

            // Obtener el promedio de calificacion

            $total = 0;

            foreach ($evaluacion_superior as $item) {

                $total += $item->calificacion;

            }

            if ($evaluacion_superior) {

                $colaborador->superior_promedio = $total / count($evaluacion_superior);

            }else{

                $colaborador->superior_promedio = 0;

            }

            // Colega o Equipo
            if ($colaborador->jefe) {
                
                $evaluacion_colega = app('db')->select("    SELECT *
                                                            FROM RH_RESULTADOS_TEMPORADA
                                                            WHERE TEMPORADAID = '$temporadaid'
                                                            AND NIT = '$colaborador->nit'
                                                            AND TIPOEV = 'subalterno'");

                if(!$evaluacion_colega){

                    $evaluacion_colega = app('db')->select("    SELECT *
                                                                FROM RH_RESULTADOS_TEMPORADA
                                                                WHERE TEMPORADAID = '$temporadaid'
                                                                AND NIT = '$colaborador->nit'
                                                                AND TIPOEV = 'colega'");

                }

            }else{

                $evaluacion_colega = app('db')->select("    SELECT *
                                                            FROM RH_RESULTADOS_TEMPORADA
                                                            WHERE TEMPORADAID = '$temporadaid'
                                                            AND NIT = '$colaborador->nit'
                                                            AND TIPOEV = 'colega'");

                if (!$evaluacion_colega) {
                    
                    $evaluacion_colega = app('db')->select("    SELECT *
                                                                FROM RH_RESULTADOS_TEMPORADA
                                                                WHERE TEMPORADAID = '$temporadaid'
                                                                AND NIT = '$colaborador->nit'
                                                                AND TIPOEV = 'subalterno'");

                }

            }

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

            $colaborador->calificacion = round((($colaborador->colega_promedio * intval($colaborador->porcentaje_colega)) / 100) + (($colaborador->superior_promedio * intval($colaborador->porcentaje_asesor)) / 100), 2);

            // Asignar un color

            $colaborador->calificacion = $colaborador->calificacion > 0 ? $colaborador->calificacion : null;

            if (!$colaborador->pendiente) {
                
                if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                    $colaborador->color = 'red';
    
                }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){
    
                    $colaborador->color = 'orange';
    
                }else{
    
                    $colaborador->color = 'green';
    
                }

            }else{

                $colaborador->color = 'blue';
                $colaborador->calificacion = 0;

            }

            $colaborador->motivo = null;

            return $colaborador;

        }

        public function sso($data){

            // Mes actual
            $month = $data["month"];

            $colaborador = $data["colaborador"];

            $result = app('db')->select("   SELECT 
                                                COUNT(*) AS TOTAL, 
                                                SUM(CASE WHEN CUMPLIO = 'S' THEN 1 ELSE 0 END) AS CUMPLIO
                                            FROM RRHH_IND_ACTIVIDAD_RESPONSABLE T1
                                            INNER JOIN RRHH_IND_ACTIVIDAD T2
                                            ON T1.ID_ACTIVIDAD = T2.ID
                                            WHERE T1.ID_PERSONA = '$colaborador->nit'
                                            AND TO_CHAR(T2.FECHA_CUMPLIMIENTO, 'YYYY-MM') = '$month'");

            if ($result) {
                
                $total = $result[0]->total;
                $cumplio = $result[0]->cumplio;

                if ($cumplio) {
                    
                    $colaborador->calificacion = ($cumplio / $total) * 100;
                    $colaborador->pendiente = false;

                }else{

                    $colaborador->calificacion = null;
                    $colaborador->pendiente = true;

                }

            }else{

                //$colaborador->calificacion = 0;
                $colaborador->pendiente = true;

            }

            // Asignar un color

            if (!$colaborador->pendiente) {
                           
                if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                    $colaborador->color = 'red';

                }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){

                    $colaborador->color = 'orange';

                }else{

                    $colaborador->color = 'green';

                }

            }else{

                $colaborador->color = 'blue';
                $colaborador->calificacion = 0;

            }

            $colaborador->motivo = null;

            return $colaborador;

        }

        public function convivencia($data){

            // Mes actual
            $month = $data["month"];

            $colaborador = $data["colaborador"];
            $criterio = $data["criterio"];

            $result = app('db')->select("   SELECT 
                                                T1.*, T2.NOMBRE AS TIPO
                                            FROM RC_RECORD T1
                                            INNER JOIN RC_TIPO T2
                                            ON T1.ID_TIPO = T2.ID_TIPO
                                            WHERE T1.NIT = '$colaborador->nit'
                                            AND T1.ID_TIPO IN (7,8)
                                            AND TO_CHAR(T1.FECHA, 'YYYY-MM') = '$month'
                                            AND T1.ELIMINADO = 0");

            $motivos = null;

            foreach ($result as $item) {
                
                $motivos = $motivos . $item->tipo . ' ';    

            }

            $colaborador->calificacion = 100 - (3 * count($result));

            if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                $colaborador->color = 'red';

            }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){

                $colaborador->color = 'orange';

            }else{

                $colaborador->color = 'green';

            }

            $colaborador->motivo = $motivos;

            return $colaborador;

        }

        public function competencia($data){

            // Mes actual
            $month = $data["month"];

            $colaborador = $data["colaborador"];

            $result = app('db')->select("   SELECT *
                                            FROM RRHH_IND_EVA_COMPETENCIA
                                            WHERE ID_PERSONA = '$colaborador->nit'
                                            AND POSPONER IS NULL
                                            ORDER BY ID DESC");

            if ($result) {
                
                // $total = 0;

                // foreach ($result as $evaluacion) {
                    
                //     $total += $evaluacion->calificacion;

                // }

                // $colaborador->calificacion = $total / count($result);
                $colaborador->calificacion = $result[0]->calificacion;
                $colaborador->pendiente = false;

            }else{

                $colaborador->calificacion = 0;
                $colaborador->pendiente = true;

            }

            if (!$colaborador->pendiente) {
                
                if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                    $colaborador->color = 'red';

                }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){

                    $colaborador->color = 'orange';

                }else{

                    $colaborador->color = 'green';

                }

            }else{

                $colaborador->color = 'blue';

            }

            return $colaborador;


        }

        public function sgs($data){

            $month = $data["month"];
            $criterio = $data["criterio"];
            $colaborador = $data["colaborador"];

            $evaluacion = app('db')->select("   SELECT *
                                                FROM RRHH_IND_SGS_EVALUACION
                                                WHERE MES = '$month'
                                                AND DELETED_AT IS NULL
                                                ORDER BY ID DESC");

            // Buscar si en la evaluación el colaborador es tomado en cuenta

            if(!$evaluacion){

                if($criterio->calificacion_default){

                    $colaborador->calificacion = 100;
                    $colaborador->pendiente = false;
                    $colaborador->color = 'green';

                    return $colaborador;
                    
                }

                $colaborador->calificacion = 0;
                $colaborador->pendiente = true;
                $colaborador->color = 'blue';

            }else{

                $evaluacion = $evaluacion[0];

                $query = "  SELECT 
                                PORCENTAJE
                            FROM RRHH_IND_SGS_EVA_ACT T1
                            INNER JOIN RRHH_IND_SGS_EVA_ACT_RESP T2
                            ON T1.ID = T2.ID_ACTIVIDAD_EVALUACION
                            WHERE ID_EVALUACION = $evaluacion->id
                            AND RESPONSABLE = '$colaborador->nit'";

                $actividades = app('db')->select("  SELECT 
                                                        PORCENTAJE, 
                                                        REALIZADA
                                                    FROM RRHH_IND_SGS_EVA_ACT T1
                                                    INNER JOIN RRHH_IND_SGS_EVA_ACT_RESP T2
                                                    ON T1.ID = T2.ID_ACTIVIDAD_EVALUACION
                                                    WHERE ID_EVALUACION = $evaluacion->id
                                                    AND RESPONSABLE = '$colaborador->nit'");

                if($actividades){

                    $calificacion = 100;

                    $colaborador->pendiente = false;

                    foreach ($actividades as $actividad) {

                        if ($actividad->realizada != 'S') {
                            
                            $calificacion -= $actividad->porcentaje;

                        }
                    }

                    $colaborador->calificacion = $calificacion;

                    if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                        $colaborador->color = 'red';
    
                    }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){
    
                        $colaborador->color = 'orange';
    
                    }else{
    
                        $colaborador->color = 'green';
    
                    }
                    
                }else{

                    $colaborador->calificacion = 100;
                    $colaborador->pendiente = false;
                    $colaborador->color = 'green';

                }

            }

            return $colaborador;

        }

        public function indicador_individual(Request $request){

            $month = $request->fecha;

            $empleado = Empleado::where('nit', $request->nit)->where('status', 'A')->first();

            $criterios = Criterio::all();

            $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;
            $empleado->criterios = $criterios;

            // Por cada uno de los criterios 
            foreach ($empleado->criterios as &$criterio) {
                
                $area = Area::find($empleado->codarea);

                if ($area->iso == '1') {
                    
                    $valor_criterio = $criterio->valor;

                }else{

                    $valor_criterio = $criterio->valor_no_iso;

                }

                $imagen = app('db')->select("   SELECT *
                                                FROM RH_RUTA_PDF
                                                WHERE NIT = '$empleado->nit'
                                                AND IDCAT = '11'");

                if ($imagen) {

                    $empleado->imagen64 =  'http://172.23.25.31/GestionServicios/' . $imagen[0]->ruta;

                }else{

                    $empleado->imagen = null;
                    $empleado->imagen64 = null;

                }

                if (!$criterio->funcion_calculo) {
                        
                    // Buscar la última evaluación según el criterio
                    $evaluacion = app('db')->select("   SELECT *
                                                        FROM RRHH_IND_EVALUACION
                                                        WHERE ID_CRITERIO = $criterio->id
                                                        AND ID_PERSONA = '$empleado->nit'
                                                        AND MES = '$month'");

                    if ($evaluacion) {
                        
                        // Calcular la calificación

                        $evaluacion = $evaluacion[0];

                        $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                        $total = 0;
                        $str_motivo = null;

                        foreach ($detalle as $item) {
                            
                            $total += $item->calificacion;

                            if ($item->motivo) {
                                
                                $str_motivo = $str_motivo . $item->motivo;

                            }

                        }

                        $criterio->motivo = $str_motivo;

                        $criterio->calificacion = $evaluacion->calificacion;

                        $criterio->calificacion = $criterio->calificacion > 100 ? 100 : $criterio->calificacion;

                        if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                        
                            $criterio->color = 'red';

                        }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){

                            $criterio->color = 'orange';

                        }else{

                            $criterio->color = 'green';

                        }

                        $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);

                    }else{

                        /*
                            Si no tiene evaluación validar si es necesario asignarle una nota por defecto    
                        */

                        if ($criterio->calificacion_default) {
                            
                            $criterio->pendiente = false;
                            $criterio->calificacion = 100;

                            if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                        
                                $criterio->color = 'red';

                            }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){

                                $criterio->color = 'orange';

                            }else{

                                $criterio->color = 'green';

                            }

                            /*
                                Validar si se trata de un proceso ISO o no
                            */

                            $area = Area::find($empleado->codarea);

                            if ($area->iso == '1') {
                                
                                $valor_criterio = $criterio->valor;

                            }else{

                                $valor_criterio = $criterio->valor_no_iso;

                            }

                            $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);

                        }else{

                            $criterio->pendiente = true;

                        }

                    }

                    $empleado->total_anual = 50;

                }else{

                    $data = [
                        "colaborador" => $empleado,
                        "criterio" => $criterio,
                        "month" => $month
                    ];

                    $result = $this->{$criterio->funcion_calculo}($data);

                    $criterio->color = $result["color"];
                    $criterio->calificacion = $result["calificacion"];
                    $criterio->pendiente = $result["pendiente"];

                    $empleado->total_mensual += round(($valor_criterio * $criterio->calificacion) / 100, 2);

                }

            }

            $empleado->stars = ($empleado->total_mensual * 5) / 100;
            $empleado->total_anual = 0;

            return response()->json($empleado);

        }

        public function equipo_indicadores(Request $request){

            $empleado = Empleado::where('nit', $request->nit)->first();
            $area_empleado = Area::find($empleado->codarea);

            $equipo = [];

            $result = app('db')->select("    SELECT 
                                                T1.NIT,
                                                T1.NOMBRE, 
                                                T1.APELLIDO, 
                                                T2.*, 
                                                T1.STATUS, 
                                                T1.JEFE
                                            FROM RH_EMPLEADOS T1
                                            INNER JOIN RH_AREAS T2
                                            ON T1.CODAREA = T2.CODAREA
                                            WHERE T1.NIT = '$request->nit'");

            $equipo [] = $result[0];

            /*
            $result = app('db')->select("   SELECT 
                                                T1.NIT,
                                                T1.NOMBRE, 
                                                T1.APELLIDO, 
                                                T2.*, 
                                                T1.STATUS, 
                                                T1.JEFE
                                            FROM RH_EMPLEADOS T1
                                            INNER JOIN RH_AREAS T2
                                            ON T1.CODAREA = T2.CODAREA
                                            WHERE T1.DEPENDE = '$request->nit'
                                            AND T1.STATUS = 'A'");
            */

            $areas = app('db')->select("   SELECT 
                                                DISTINCT(T1.CODAREA), 
                                                T2.DESCRIPCION
                                            FROM RH_EMPLEADOS T1
                                            INNER JOIN RH_AREAS T2
                                            ON T1.CODAREA = T2.CODAREA
                                            WHERE T1.DEPENDE = '$request->nit'");

            /*
                Por cada equipo buscar los integrantes
            */

            // foreach ($result as $integrante) {
                
            //     $equipo [] = $integrante;

            // }

            foreach ($areas as $area) {
                
                /*
                    Buscar al Asesor
                */

                $jefe = app('db')->select(" SELECT NOMBRE, APELLIDO
                                            FROM RH_EMPLEADOS
                                            WHERE CODAREA =  '$area->codarea'
                                            AND STATUS = 'A'
                                            AND JEFE = '1'");

                if ($jefe) {
                    
                    $area->jefe = $jefe[0];

                }

                $integrantes = app('db')->select("  SELECT 
                                                        NIT
                                                    FROM RH_EMPLEADOS
                                                    WHERE CODAREA = '$area->codarea'
                                                    AND STATUS = 'A'");

                $area->integrantes = $integrantes;

            }

            $data = [
                "empleado" => $empleado,
                "area" => $area_empleado,
                "equipo" => $areas
            ];

            return response()->json($data);

        }

        public function export_dashboard(Request $request){
            
            $excel_export = new DashboardExport($request);

            return Excel::download($excel_export, 'dashboard.xlsx');

        }

        public function export_dashboard_view(Request $request){

            $result = $this->dashboard_area($request);

            $areas = $result->getData();
            
            $data = [
                "areas" => $areas
            ];
            
            return response(view('export_dashboard', $data));

        }

        public function puntaje_anual(Request $request){

            /* Ejecutar el job para tener información para calcular la nota */

            /*
                Se deberá de ejecutar el job de manera individual para hacerlo en los meses que aplique
                La función para obtener los datos del dashboard se ejecutará de igual forma por cada colaborador, para obtener solo de los meses que aplique
            */

            $year_start = date('Y-01');
            $year = date('Y');
            $current_month = date('m');

            $areas = $request->areas;

            if ($request->areas) {
                
                foreach ($areas as &$area) {
                    
                    $area = (object) $area;

                    foreach ($area->empleados as &$empleado) {
                    
                        $empleado = (object) $empleado;

                        $empleado->mes_inicio = date('Y-m', strtotime($empleado->fecha_ingreso));
                        
                        if (strtotime($empleado->mes_inicio) > strtotime($year_start)) {
                            
                            $empleado->all_year = false;

                        }else{
                            
                            $empleado->all_year = true;

                        }

                        $months = [];
                        $length = 2;
                        $type = 'd';
                        $char = 0;
                        $format = "%{$char}{$length}{$type}";

                        $split_fecha_ingreso = explode("-", $empleado->mes_inicio);
                        $suma_promedio = 0;

                        for ($i = $empleado->all_year ? 1 : intval($split_fecha_ingreso[1]); $i <= intval($current_month); $i++) { 
                            
                            $month_year = $year . '-' . sprintf($format, $i); 

                            /* 
                                Ejecutar el job por cada colaborador y por cada mes del cual se desea conocer la nota mensual
                            */

                            $data = (object) [
                                "date" => $month_year,
                                "nit" => $empleado->nit,
                                "codarea" => null
                            ];

                            \Queue::push(new EvaluacionJob($data));

                            $request_evaluacion = new Request();

                            $request_evaluacion->replace([
                                "nit" => $empleado->nit,
                                "fecha" => $month_year
                            ]);

                            $result_evaluacion = $this->indicador_individual($request_evaluacion);

                            $data_result = $result_evaluacion->getData();

                            $empleado->result_evaluacion = $result_evaluacion->getData();

                            $months [] = $month_year . ': ' . $data_result->total_mensual;

                            $suma_promedio += $data_result->total_mensual;
                        }

                        $empleado->dates = $months;
                        $empleado->loading_anual = false;
                        $empleado->total_anual = $suma_promedio / count($months);
                        
                    }

                }

                return response()->json($areas);

            }elseif($request->empleado){
                
                $empleado = (object) $request->empleado;

                $empleado->mes_inicio = date('Y-m', strtotime($empleado->fecha_ingreso));
                        
                if (strtotime($empleado->mes_inicio) > strtotime($year_start)) {
                    
                    $empleado->all_year = false;

                }else{
                    
                    $empleado->all_year = true;

                }

                $months = [];
                $length = 2;
                $type = 'd';
                $char = 0;
                $format = "%{$char}{$length}{$type}";

                $split_fecha_ingreso = explode("-", $empleado->mes_inicio);
                $suma_promedio = 0;

                for ($i = $empleado->all_year ? 1 : intval($split_fecha_ingreso[1]); $i <= intval($current_month); $i++) { 
                    
                    $month_year = $year . '-' . sprintf($format, $i); 

                    /* 
                        Ejecutar el job por cada colaborador y por cada mes del cual se desea conocer la nota mensual
                    */

                    $data = (object) [
                        "date" => $month_year,
                        "nit" => $empleado->nit,
                        "codarea" => null
                    ];

                    \Queue::push(new EvaluacionJob($data));

                    $request_evaluacion = new Request();

                    $request_evaluacion->replace([
                        "nit" => $empleado->nit,
                        "fecha" => $month_year
                    ]);

                    $result_evaluacion = $this->indicador_individual($request_evaluacion);

                    $data_result = $result_evaluacion->getData();

                    $empleado->result_evaluacion = $result_evaluacion->getData();

                    $months [] = $month_year . ': ' . $data_result->total_mensual;

                    $suma_promedio += $data_result->total_mensual;
                }

                $empleado->dates = $months;
                $empleado->loading_anual = false;
                $empleado->total_anual = $suma_promedio / count($months);
                
                return response()->json($empleado);

            }

        }

    }

?>