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

                        $criterio->calificacion = round(($total / $criterio->valor) * 100, 2);

                        $criterio->calificacion = $criterio->calificacion > 100 ? 100 : $criterio->calificacion;

                        if ($criterio->calificacion >= 0 && $criterio->calificacion < 60) {
                        
                            $criterio->color = 'red';

                        }elseif( $criterio->calificacion >= 60 && $criterio->calificacion < 80){

                            $criterio->color = 'orange';

                        }else{

                            $criterio->color = 'green';

                        }

                    }else{

                        $criterio->pendiente = true;

                    }

                    
                    $empleado->total_mensual += round(($criterio->valor * $criterio->calificacion) / 100, 2);
                    $empleado->total_anual = 50;
                    
                }

                $empleado->stars = ($empleado->total_mensual * 5) / 100;

            }

            return response()->json($empleados);

        }

    }

?>