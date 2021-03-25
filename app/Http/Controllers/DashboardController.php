<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Area;
    use App\Empleado;
    use App\Criterio;
    use App\DetalleEvaluacion;

    class DashboardController extends Controller{

        public function dashboard_area(Request $request){

            // Mes actual
            $month = date('m');

            $empleados = Empleado::where('codarea', $request->codarea)->where('status', 'A')->get();

            // Obtener los criterios para el dashboard

            foreach ($empleados as &$empleado) {
                
                $criterios = Criterio::all();

                $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;
                $empleado->criterios = $criterios;

                // Por cada uno de los criterios 
                foreach ($empleado->criterios as &$criterio) {
                    
                    if (!$criterio->funcion_calculo) {
                        
                        // Buscar la última evaluación según el criterio
                        $evaluacion = app('db')->select("   SELECT *
                                                            FROM RRHH_IND_EVALUACION
                                                            WHERE ID_CRITERIO = $criterio->id
                                                            AND ID_PERSONA = '$empleado->nit'
                                                            AND TO_CHAR(CREATED_AT, 'MM') = '$month'");

                        if ($evaluacion) {
                            
                            // Calcular la calificación

                            $evaluacion = $evaluacion[0];

                            $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                            $total = 0;

                            foreach ($detalle as $item) {
                                
                                $total += $item->calificacion;

                            }

                            $criterio->calificacion = round(($total / $evaluacion->valor_criterio) * 100, 2);

                            $criterio->calificacion = $criterio->calificacion > 100 ? 100 : $criterio->calificacion;

                            if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                            
                                $criterio->color = 'red';

                            }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){

                                $criterio->color = 'orange';

                            }else{

                                $criterio->color = 'green';

                            }

                            $empleado->total_mensual += round(($evaluacion->valor_criterio * $criterio->calificacion) / 100, 2);

                        }else{

                            $criterio->pendiente = true;

                        }

                        $empleado->total_anual = 50;

                    }else{

                        $data = [
                            "colaborador" => $empleado,
                            "criterio" => $criterio
                        ];

                        $result = $this->{$criterio->funcion_calculo}($data);

                        $criterio->color = $result["color"];
                        $criterio->calificacion = $result["calificacion"];

                        /*
                            TODO
                            - Tomar el valor del criterio si es ISO o no
                        */

                        $empleado->total_mensual += round(($criterio->valor * $criterio->calificacion) / 100, 2);

                    }
                    
                }

                $empleado->stars = ($empleado->total_mensual * 5) / 100;

            }

            return response()->json($empleados);

        }
        
        public function performance($data){

            $colaborador = $data["colaborador"];

            // Obtener los porcentaje de evaluación
            $result = app('db')->select("   SELECT *
                                            FROM RH_COLABORADORES_TEMPORADA
                                            WHERE NIT = '$colaborador->nit'
                                            ORDER BY TEMPORADAID DESC");
            
            if ($result) {
                
                $colaborador->porcentaje_colega = $result[0]->porcentaje_colega;
                $colaborador->porcentaje_asesor = $result[0]->porcentaje_asesor;
                $colaborador->temporadaid = $result[0]->temporadaid;

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

            // Colega
            $evaluacion_colega = app('db')->select("    SELECT *
                                                        FROM RH_RESULTADOS_TEMPORADA
                                                        WHERE TEMPORADAID = '$colaborador->temporadaid'
                                                        AND NIT = '$colaborador->nit'
                                                        AND TIPOEV = 'colega'");

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

            if ($colaborador->calificacion >= 0 && $colaborador->calificacion < 60) {

                $colaborador->color = 'red';

            }elseif( $colaborador->calificacion >= 60 && $colaborador->calificacion < 80){

                $colaborador->color = 'orange';

            }else{

                $colaborador->color = 'green';

            }

            return $colaborador;

        }

    }

?>