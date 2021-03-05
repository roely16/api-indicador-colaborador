<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;

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
                
                $item->check = false;

            }

            $data = [
                "criterio" => $criterio,
                "items" => $items
            ];

            return response()->json($data);

        }

    }

?>