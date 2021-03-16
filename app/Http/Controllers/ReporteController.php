<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;
    use App\Ponderacion;
    use App\Empleado;
    use App\Area;

    class ReporteController extends Controller{

        public function obtener_reportes(Request $request){

            $items = [];

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "colaborador"
                ],
                [
                    "text" => "Fecha",
                    "value" => "fecha"
                ],
                [
                    "text" => "Calificación",
                    "value" => "calificacion"
                ],
                [
                    "text" => "Acción",
                    "value" => "action"
                ]
            ];

            $data = [
                "items" => $items,
                "headers" => $headers
            ];

            return response()->json($data);

        }

        public function datos_reporte(Request $request){

            // Obtener la información del colaborador
            $colaborador = Empleado::where('nit', $request->nit)->first();

            // Obtener la información de la sección
            $area = Area::find($colaborador->codarea);

            $criterio = Criterio::where('modulo', $request->url)->first();

            $criterio->valor = $area->iso == '1' ? $criterio->valor : $criterio->valor_no_iso;

            // Obtener los criterios dependiendo si es asesor o colaborador

            if ($colaborador->jefe == '1') {
                
                $items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_asesor', 'S')->get();

            }else{

                $items = CriterioItem::where('id_criterio', $criterio->id)->where('aplica_prestador', 'S')->get();

            }

            foreach ($items as $item) {
                
                // Si el metodo de evaluación es ponderación
                if ($criterio->metodo_calificacion == 'ponderacion') {
                    
                    $item->calificaciones = Ponderacion::where('id_criterio_item', $item->id)->orderBy('valor', 'desc')->get();

                    $item->calificacion = null;

                }else{

                    $item->calificacion = 100;
                }

                // Especificar cual será el valor
                if ($area->iso == '1') {
                    
                    // Si es ISO seleccionar el valor dependiendo si es asesor o colaborador
                    $item->valor = $colaborador->jefe == '1' ? $item->valor : $item->valor_p;

                }else{

                    // Si no es ISO asignar 
                    $item->valor = $colaborador->jefe == '1' ? $item->valor_no_iso : $item->valor_no_iso_p;

                }


                $item->check = false;
                $item->show_description = false;
                $item->editable = false;

            }

            $data = [
                "criterio" => $criterio,
                "items" => $items,
                "detalle_colaborador" => [
                    "iso" => $area->iso,
                    "asesor" => $colaborador->jefe
                ]
            ];

            return response()->json($data);

        }

    }

?>