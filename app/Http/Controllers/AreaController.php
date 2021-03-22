<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Area;
    use App\Empleado;

    class AreaController extends Controller{

        public function obtener_areas(Request $request){

            $menu = Area::where('estatus', 'A')->get();
            
            return response()->json($menu);

        }

        public function obtener_colaboradores(Request $request){

            $empleados = Empleado::where('codarea', $request->codarea)->where('status', 'A')->get();

            foreach ($empleados as &$empleado) {
                
                $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;

            }

            return response()->json($empleados);

        }

        public function obtener_areas_colaboradores(Request $request){

            $areas = Area::where('estatus', 'A')->get();

            foreach ($areas as &$area) {
                
                $empleados = Empleado::where('codarea', $area->codarea)->where('status', 'A')->get();

                foreach ($empleados as &$empleado) {
                    
                    $empleado->color_card = null;

                }

                $area->empleados = $empleados;
                $area->color_card = null;
                $area->drag = true;
                
                $area->expand = false;

            }

            return response()->json($areas);

        }

    }

?>