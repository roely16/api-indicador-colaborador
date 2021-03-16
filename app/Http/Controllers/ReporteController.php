<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;

    use App\Ponderacion;

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

            $criterio = Criterio::where('modulo', $request->url)->first();

            $items = CriterioItem::where('id_criterio', $criterio->id)->get();

            foreach ($items as $item) {
                
                // Si el metodo de evaluación es ponderación
                if ($criterio->metodo_calificacion == 'ponderacion') {
                    
                    $item->calificaciones = Ponderacion::where('id_criterio_item', $item->id)->orderBy('valor', 'desc')->get();

                    $item->calificacion = null;

                }else{

                    $item->calificacion = 100;
                }

                $item->check = false;
                $item->show_description = false;
                $item->value = 0;
                $item->editable = false;

            }

            $data = [
                "criterio" => $criterio,
                "items" => $items
            ];

            return response()->json($data);

        }

    }

?>