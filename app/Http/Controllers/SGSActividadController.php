<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\ActividadSGS;

    class SGSActividadController extends Controller{
        
        public function obtener_actividades(Request $request){

            $actividades = ActividadSGS::all();

            $actividades = app('db')->select("  SELECT 
                                                    ID,
                                                    NOMBRE,
                                                    DESCRIPCION,
                                                    TO_CHAR(UPDATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS UPDATED_AT
                                                FROM RRHH_IND_SGS_ACTIVIDAD
                                                WHERE DELETED_AT IS NULL
                                                ORDER BY ID DESC");

            $headers = [
                [
                    "text" => "Nombre",
                    "value" => "nombre",
                    "sortable" => false,
                    "width" => "30%"
                ],
                [
                    "text" => "Descripción",
                    "value" => "descripcion",
                    "sortable" => false,
                    "width" => "30%"
                ],
                [
                    "text" => "Última actualización",
                    "value" => "updated_at",
                    "sortable" => false,
                    "width" => "30%"
                ],
                [
                    "text" => "Acciones",
                    "value" => "action",
                    "sortable" => false,
                    "align" => "right"
                ]
            ];

            $response = [
                "items" => $actividades,
                "headers" => $headers
            ];

            return response()->json($response);

        }

        public function registrar_actividad(Request $request){

            $actividad = new ActividadSGS();

            $actividad->nombre = $request->nombre;
            $actividad->descripcion = $request->descripcion;
            $actividad->save();

            $response = [
                "status" => 200,
                "actividad" => [
                    "id" => $actividad->id,
                    "nombre" => $actividad->nombre,
                    "descripcion" => $actividad->descripcion,
                    "updated_at" => date( "d/m/Y H:i:s", strtotime($actividad->updated_at))
                ]
            ];

            return response()->json($response);

        }

        public function eliminar_actividad(Request $request){

            $actividad = ActividadSGS::find($request->id);
            $actividad->delete();

            $response = [
                "status" => 200,
                "actividad" => $actividad
            ];

            return response()->json($response);

        }

        public function detalle_actividad(Request $request){

            $actividad = ActividadSGS::find($request->id);

            $response = [
                "status" => 200,
                "actividad" => $actividad
            ];

            return response()->json($response);

        }

        public function editar_actividad(Request $request){

            $actividad = ActividadSGS::find($request->id);
            $actividad->nombre = $request->nombre;
            $actividad->descripcion = $request->descripcion;
            $actividad->save();

            $response = [
                "status" => 200,
                "actividad" => [
                    "id" => $actividad->id,
                    "nombre" => $actividad->nombre,
                    "descripcion" => $actividad->descripcion,
                    "updated_at" => date( "d/m/Y H:i:s", strtotime($actividad->updated_at))
                ]
            ];

            return response()->json($response);

        }

    }

?>