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
                    
                    // Obtener la imagen de cada colaborador
                    $imagen = app('db')->select("   SELECT *
                                                    FROM RH_RUTA_PDF
                                                    WHERE NIT = '$empleado->nit'
                                                    AND IDCAT = '11'");

                    if ($imagen) {
                                    
                        $empleado->imagen64 =  'http://172.23.25.31/GestionServicios/' . $imagen[0]->ruta;

                        //$empleado->imagen64 = $_SERVER['DOCUMENT_ROOT'] . "/GestionServicios/" . $empleado->imagen;

                        // $type = pathinfo($empleado->imagen, PATHINFO_EXTENSION);
                        
                        // try {
                            
                        //     $data = file_get_contents($empleado->imagen);
                        //     $empleado->imagen64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

                        // } catch (\Throwable $th) {
                        //     //throw $th;
                        // }
                        

                    }else{

                        $empleado->imagen = null;
                        $empleado->imagen64 = null;

                    }

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