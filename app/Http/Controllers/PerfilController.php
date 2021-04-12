<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Perfil;
    use App\Competencia;
    use App\Empleado;


    class PerfilController extends Controller{

        public function obtener_perfiles(Request $request){

            $items = [];

            $headers = [
                [
                    "text" => "Perfil",
                    "value" => "nombre",
                    "sortable" => false,
                    "width" => "40%"
                ],
                [
                    "text" => "Fecha de Creación",
                    "value" => "fecha",
                    "width" => "20%"
                ],
                [
                    "text" => "No. de Colaborador",
                    "value" => "colaboradores",
                    "width" => "20%"
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "align" => "end",
                    "width" => "20%"
                ]
            ];

            $data = [
                "items" => $items,
                "headers" => $headers
            ];

            return response()->json($data);
            
        }

        public function registrar(Request $request){

            $perfil = new Perfil();

            $perfil->nombre = $request->nombre;
            $perfil->descripcion = $request->descripcion;
            $perfil->save();

            // Registrar las competencias
            foreach ($request->tipos_competencias as $tipo) {
                
                foreach ($tipo["competencias"] as $competencia) {
                    
                    //return response()->json($competencia["nombre"]);

                    $n_competencia = new Competencia();
                    
                    $n_competencia->nombre = $competencia["nombre"];
                    $n_competencia->id_tipo = $competencia["id_tipo"];
                    $n_competencia->id_perfil = $perfil->id;

                    $n_competencia->save();

                }

            }

            return response()->json($competencia);

        }

        public function detalle(Request $request){

            return response()->json($request);

        }

        public function editar(Request $request){

            return response()->json($request);

        }

        public function eliminar(Request $request){

            return response()->json($request);

        }

        public function info_colaborador(Request $request){

            $colaborador = Empleado::where('nit', $request->nit)->first();

            return response()->json($colaborador);

        }

    }