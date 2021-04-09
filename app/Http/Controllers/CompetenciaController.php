<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\TipoCompetencia;
    use App\Competencia;
    use App\Empleado;
    use App\Perfil;

    class CompetenciaController extends Controller{

        public function obtener_perfil(Request $request){

            $empleado = Empleado::where('nit', $request->nit)->first();

            $perfil = Perfil::find($empleado->id_perfil);

            $tipos_competencias = TipoCompetencia::all();

            foreach ($tipos_competencias as &$tipo) {
                
                $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $empleado->id_perfil)->get();

                $tipo->competencias = $competencias;

            }

            $data = [
                "empleado" => $empleado,
                "tipos_competencias" => $tipos_competencias,
                "perfil" => $perfil
            ];

            return response()->json($data);

        }

    }