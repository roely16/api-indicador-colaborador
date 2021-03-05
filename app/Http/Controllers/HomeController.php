<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Menu;

    class HomeController extends Controller{

        public function obtener_menu(Request $request){

            $menu = Menu::all();

            return response()->json($menu);

        }

    }

?>