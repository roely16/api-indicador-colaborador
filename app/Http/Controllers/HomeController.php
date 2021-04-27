<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Menu;
    use App\Permiso;

    class HomeController extends Controller{

        public function obtener_menu(Request $request){

            $permisos = app('db')->select(" SELECT 
                                                T1.*, 
                                                T2.ACCESO AS MENU_ACCESO, 
                                                T2.ESCRITURA AS MENU_ESCRITURA, 
                                                T2.SECCIONES AS MENU_SECCIONES,
                                                T2.CONF AS MENU_CONF
                                            FROM RRHH_IND_PERMISO T1
                                            INNER JOIN RRHH_IND_MENU T2
                                            ON T1.ID_MENU = T2.ID
                                            WHERE T2.OCULTAR IS NULL
                                            AND T1.ID_PERSONA = '$request->nit'");

            //$permisos = Permiso::where('id_persona', $request->nit)->orderBy('id_menu', 'asc')->get();

            foreach ($permisos as &$permiso) {
                
                $menu = Menu::find($permiso->id_menu);

                $permiso->color = $menu->color;
                $permiso->descripcion = $menu->descripcion;
                $permiso->icono = $menu->icono;
                $permiso->nombre = $menu->nombre;
                $permiso->url = $menu->url;
                $permiso->ocultar = $menu->ocultar;

            }
            //$menu = Menu::all();

            return response()->json($permisos);

        }

    }

?>