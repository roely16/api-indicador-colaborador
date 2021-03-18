<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Grupo;
    use App\Integrante;

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
                
                // Buscar los integrantes del grupo
                $integrantes = app('db')->select("  SELECT *
                                                    FROM RH_EMPLEADOS T1
                                                    INNER JOIN RRHH_IND_INT_GRUPO T2
                                                    ON T1.NIT = T2.ID_PERSONA");

                $grupo->expand = false;
                $grupo->integrantes = $integrantes;

            }

            return response()->json($grupos);

        }

        public function agregar_integrante(Request $request){

            $integrante = new Integrante();

            $integrante->id_grupo = $request->id_grupo;
            $integrante->id_persona = $request->id_persona;
            $integrante->save();

            return response()->json($integrante);

        }

    }

?>