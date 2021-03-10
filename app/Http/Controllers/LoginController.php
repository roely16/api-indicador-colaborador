<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class LoginController extends Controller{
       
        public function __construct(){
            
        }

        public function login(Request $request){

            $result = app('db')->select("   SELECT *
                                            FROM RH_EMPLEADOS
                                            WHERE USUARIO = UPPER('$request->usuario')
                                            AND DESENCRIPTAR(PASS) = '$request->pass'");

            if (empty($result)) {
                
                $data = [
                    "status" => 100,
                    "title" => "Error",
                    "message" => "Usuario o contraseña incorrectos",
                    "type" => "error"
                ];

                return response()->json($data);

            }

            $result = $result[0];

            // Buscar la sección
            $seccion = app('db')->select("  SELECT *
                                            FROM RH_AREAS
                                            WHERE CODAREA = $result->codarea");

            $result->seccion = $seccion[0];

            $data = [
                "status" => 200,
                "data" => $result
            ];

            return response()->json($data);

        }

        
    }

?>