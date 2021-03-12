<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Menu;
    use App\Permiso;

    class HomeController extends Controller{

        public function obtener_menu(Request $request){

            $permisos = Permiso::where('id_persona', $request->nit)->orderBy('id_menu', 'asc')->get();

            foreach ($permisos as &$permiso) {
                
                $menu = Menu::find($permiso->id_menu);

                $permiso->color = $menu->color;
                $permiso->descripcion = $menu->descripcion;
                $permiso->icono = $menu->icono;
                $permiso->nombre = $menu->nombre;
                $permiso->url = $menu->url;

            }
            //$menu = Menu::all();

            return response()->json($permisos);

        }

    }

?>