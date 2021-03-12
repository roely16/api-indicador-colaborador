<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Menu;
    use App\Permiso;

    class PermisoController extends Controller{

        public function obtener_permisos_usuario(Request $request){

            $menu = Menu::all();

            foreach ($menu as &$item) {
                
                $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu',$item->id)->first();
                
                $item->acceso = $permiso ? true : false;
                $item->escritura = $permiso ? $permiso->escritura == 'S' ? true : false : false;
                $item->secciones = $permiso ? $permiso->secciones == 'S' ? true : false : false;

            }

            return response()->json($menu);

        }

        public function registrar_permisos(Request $request){

            $permisos = $request->permisos;
            $nit = $request->nit;
            $codarea = $request->codarea;

            foreach ($permisos as &$permiso) {
                
                if ($permiso["acceso"]) {
                    
                    /* Buscar si ya estaba habilitado el permiso */
                    $permiso_r = Permiso::where('id_persona', $nit)->where('id_menu', $permiso["id"])->first();

                    $escritura = $permiso["escritura"] ? 'S' : 'N';
                    $secciones = $permiso["secciones"] ? 'S' : 'N';

                    if ($permiso_r) {
                        
                        // Actualizar
                        $permiso_r->escritura = $escritura;
                        $permiso_r->secciones = $secciones;
                        $result = $permiso_r->save();

                    }else{

                        // Registrar uno nuevo

                        $nuevo_permiso = new Permiso();
                        $nuevo_permiso->id_persona = $nit;
                        $nuevo_permiso->id_menu = $permiso["id"];
                        $nuevo_permiso->escritura = $escritura;
                        $nuevo_permiso->secciones = $secciones;

                        $result = $nuevo_permiso->save();

                    }

                }else{

                    // Validar si existe para eliminar
                    $permiso_r = Permiso::where('id_persona', $nit)->where('id_menu', $permiso["id"])->first();

                    if ($permiso_r) {

                        $result = $permiso_r->delete();

                    }

                }

            }

            if ($result) {
                
                $data = [
                    "title" => "Excelente",
                    "message" => "Los permisos han sido habilitados exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function permisos_habilitados(Request $request){

            $colaboradores = app('db')->select("    SELECT 
                                                        DISTINCT(ID_PERSONA), 
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR,
                                                        T2.CODAREA
                                                    FROM RRHH_IND_PERMISO T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT");

            foreach ($colaboradores as &$colaborador) {
                
                $last_update = app('db')->select("  SELECT TO_CHAR(UPDATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS updated_at
                                                    FROM RRHH_IND_PERMISO
                                                    WHERE ID_PERSONA = '$colaborador->id_persona'
                                                    ORDER BY ID DESC");

                $last_update = $last_update[0];

                $colaborador->updated_at = $last_update->updated_at;

            }

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "colaborador",
                    "width" => "50%"
                ],
                [
                    "text" => "Fecha de actualización",
                    "value" => "updated_at",
                    "width" => "30%",
                    "sortable" => false
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "width" => "20%",
                    "align" => "right",
                    "sortable" => false
                ]
            ];

            $data = [
                "items" => $colaboradores,
                "headers" => $headers
            ];

            return response()->json($data);

        }

        public function eliminar_permisos(Request $request){

            $deleteRows = Permiso::where('id_persona', $request->id_persona)->delete();

            if ($deleteRows <= 0) {
                
                $data = [

                    "title" => "Error",
                    "message" => "Se a generado un error al intentar eliminar los permisos",
                    "type" => "error"

                ];

                return response()->json($data);

            };

            $data = [

                "title" => "Excelente",
                "message" => "Los permisos han sido eliminados exitosamente",
                "type" => "success"

            ];

            return response()->json($data);

        }

    }

?>