<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Grupo;

    class SOController extends Controller{

        public function registrar_grupo(Request $request){

            $grupo = new Grupo();

            $grupo->nombre = $request->nombre;
            $grupo->save();

            return response()->json($grupo);

        }

        public function obtener_grupos(Request $request){

            $grupos = Grupo::orderBy('id', 'desc')->get();

            foreach ($grupos as &$grupo) {
                
                $grupo->expand = false;
                $grupo->integrantes = [];

            }

            return response()->json($grupos);

        }

    }

?>