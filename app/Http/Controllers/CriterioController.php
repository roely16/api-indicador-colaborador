<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;

    class CriterioController extends Controller{

        public function  obtener_criterios(){

            $criterios = Criterio::where('mantenimiento', 'S')->get();

            return response()->json($criterios);

        }

        public function detalle_criterio(Request $request){

            $criterio = Criterio::find($request->id_criterio);

            $detalle = app('db')->select("  SELECT 
                                                ID, 
                                                DESCRIPCION, 
                                                TO_CHAR(VALOR, '0.99') AS VALOR, 
                                                TO_CHAR(VALOR_NO_ISO, '0.99') AS VALOR_NO_ISO, 
                                                ID_CRITERIO,
                                                APLICA_ASESOR,
                                                APLICA_PRESTADOR
                                            FROM RRHH_IND_CRITERIO_ITEM
                                            WHERE ID_CRITERIO = $request->id_criterio");



            $headers = [
                [
                    "text" => "ID",
                    "value" => "id",
                    "sortable" => false,
                    "width" => "5%"
                ],
                [
                    "text" => "Descripción",
                    "value" => "descripcion",
                    "sortable" => false,
                    "width" => "50%"
                ],
                [
                    "text" => "Valor",
                    "value" => "valor",
                    "sortable" => false,
                    "width" => "20%"
                ],
                [
                    "text" => "Aplica",
                    "value" => "aplica",
                    "sortable" => false,
                    "width" => "15%"
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "align" => "right",
                    "sortable" => false,
                    "width" => "10%"
                ]
            ];

            $data = [
                "items" => $detalle,
                "headers" => $headers,
                "criterio" => $criterio
            ];

            return response()->json($data);

        }

    }

?>