<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Area;
    use App\Empleado;

    class AreaController extends Controller{

        public function obtener_areas(Request $request){

            $menu = Area::all();

            return response()->json($menu);

        }

        public function obtener_colaboradores(Request $request){

            $empleados = Empleado::where('codarea', $request->codarea)->where('status', 'A')->get();

            foreach ($empleados as &$empleado) {
                
                $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;

            }

            return response()->json($empleados);

        }

    }

?>