<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Area;
    use App\Empleado;
    use App\Criterio;
    use App\Permiso;
    use App\Menu;

    class AreaController extends Controller{

        public function obtener_areas(Request $request){

            $menu = Menu::where('url', $request->modulo)->first();
            
            $modulo = Criterio::find($menu->id_criterio);

            if ($modulo) {
                
                // Validar el permiso de acceder a todas las secciones
                $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu', $modulo->id)->where('secciones', 'S')->first();

                if ($permiso) {
                    
                    $menu = Area::where('estatus', 'A')->get();

                    return response()->json($menu);

                }

            }

            // Verificar si el módulo aplica el filtro de dependencia
            $criterio = Criterio::where('id', $menu->id_criterio)->where('filtro_depende', 'S')->first();

            if ($criterio) {
                
                // Obtener los códigos de area dependen del usuario
                $result = app('db')->select("   SELECT DISTINCT(CODAREA) AS CODAREA
                                                FROM RH_EMPLEADOS 
                                                WHERE DEPENDE = '$request->nit'
                                                AND STATUS = 'A'");

                $codareas = [];

                // Convertir en array
                foreach ($result as $codarea) {
                    
                    $codareas [] = $codarea->codarea;

                }

                //return response()->json($codareas);

                $menu = Area::where('estatus', 'A')->whereIn('codarea', $codareas)->get();

            }else{

                $menu = Area::where('estatus', 'A')->get();

            }
            
            return response()->json($menu);

        }

        public function obtener_colaboradores(Request $request){

            // $modulo = Criterio::where('modulo', $request->modulo)->first();

            $menu = Menu::where('url', $request->modulo)->first();
            
            $modulo = Criterio::find($menu->id_criterio);

            if ($modulo) {
                
                // Validar el permiso de acceder a todas las secciones
                $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu', $modulo->id)->where('secciones', 'S')->first();

                if ($permiso) {
                    
                    $empleados = Empleado::where('codarea', $request->codarea)->where('status', 'A')->get();

                    foreach ($empleados as &$empleado) {
                
                        $empleado->nombre_completo = $empleado->nombre . ' ' . $empleado->apellido;
        
                    }
        
                    return response()->json($empleados);
                    
                }

            }

            // Verificar si el módulo aplica el filtro de dependencia
            $criterio = Criterio::where('id', $menu->id_criterio)->where('filtro_depende', 'S')->first();

            if ($criterio) {
                
                $empleados = Empleado::
                                where('codarea', $request->codarea)
                                ->where('status', 'A')
                                ->where('depende', $request->nit)
                                ->get();

            }else{

                $empleados = Empleado::where('codarea', $request->codarea)->where('status', 'A')->get();

            }

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

        public function obtener_todas_areas(){

            $menu = Area::where('estatus', 'A')->get();

            return response()->json($menu);

        }

    }

?>