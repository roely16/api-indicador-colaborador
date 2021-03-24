<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Grupo;
    use App\Integrante;
    use App\Area;
    use App\Empleado;

    class SOController extends Controller{

        public function registrar_grupo(Request $request){

            $grupo = new Grupo();

            $grupo->nombre = $request->nombre;
            $grupo->save();

            return response()->json($grupo);

        }

        public function obtener_grupos(Request $request){

            $grupos = Grupo::orderBy('id', 'desc')->get();

            foreach ($grupos as &$grupo) {
                
                // Buscar los integrantes del grupo
                $secciones = app('db')->select("    SELECT 
                                                        DISTINCT(CODAREA) AS CODAREA
                                                    FROM RH_EMPLEADOS T1
                                                    INNER JOIN RRHH_IND_INT_GRUPO T2
                                                    ON T1.NIT = T2.ID_PERSONA
                                                    AND T2.ID_GRUPO = $grupo->id");

                foreach ($secciones as &$seccion) {
                    
                    $area = Area::find($seccion->codarea);

                    $seccion->nombre = $area->descripcion;

                    /* Buscar los integrantes de la sección y del grupo */

                    $integrantes = app('db')->select("  SELECT 
                                                            CONCAT(T1.NOMBRE, CONCAT(' ', T1.APELLIDO)) AS NOMBRE, 
                                                            T1.NIT
                                                        FROM RH_EMPLEADOS T1
                                                        INNER JOIN RRHH_IND_INT_GRUPO T2
                                                        ON T1.NIT = T2.ID_PERSONA
                                                        AND T2.ID_GRUPO = $grupo->id
                                                        AND T1.CODAREA = $seccion->codarea");

                    $seccion->integrantes = $integrantes;

                }

                $grupo->expand = false;
                $grupo->secciones = $secciones;
                $grupo->color_card = null;

            }

            return response()->json($grupos);

        }

        public function agregar_integrante(Request $request){

            // Validar que el integrante no exista
            $existe = Integrante::where('id_grupo', $request->id_grupo)->where('id_persona', $request->id_persona)->first();

            if (!$existe) {
                
                $integrante = new Integrante();

                $integrante->id_grupo = $request->id_grupo;
                $integrante->id_persona = $request->id_persona;
                $integrante->save();

                // Obtener al colaborador
                $empleado = Empleado::where('nit', $request->id_persona)->first();

                // Obtener la sección
                $seccion = Area::find($empleado->codarea);

                // Obtener los integrantes
                $integrantes = app('db')->select("  SELECT 
                                                        CONCAT(T1.NOMBRE, CONCAT(' ', T1.APELLIDO)) AS NOMBRE, 
                                                        T1.NIT
                                                    FROM RH_EMPLEADOS T1
                                                    INNER JOIN RRHH_IND_INT_GRUPO T2
                                                    ON T1.NIT = T2.ID_PERSONA
                                                    AND T2.ID_GRUPO = $request->id_grupo
                                                    AND T1.CODAREA = $seccion->codarea");

                $data_seccion = [
                    "codarea" => $seccion->codarea,
                    "nombre" => $seccion->descripcion,
                    "integrantes" => $integrantes
                ];

                $data = [
                    "status" => 200,
                    "data" => $data_seccion
                ];

                return response()->json($data);

            }

            $data = [
                "status" => 100,
                "title" => "Error",
                "message" => "Esta persona ya forma parte del grupo",
                "type" => "error"
            ];
            
            return response()->json($data);

        }

        public function agregar_seccion(Request $request){

            $i = 0;

            foreach ($request->empleados as $colaborador) {
                
                $existe = Integrante::where('id_grupo', $request->id_grupo)->where('id_persona', $colaborador["nit"])->first();

                if (!$existe) {

                    $integrante = new Integrante();

                    $integrante->id_grupo = $request->id_grupo;
                    $integrante->id_persona = $colaborador["nit"];
                    $integrante->save();

                    $i++;

                }

            }

            if ($i > 0) {
                
                // Retornar el grupo

                $data = [
                    "status" => 200,
                ];

            }else{

                $data = [
                    "status" => 100,
                    "title" => "Error",
                    "message" => "Esta sección ya forma parte del grupo",
                    "type" => "error"
                ];

            }

            return response()->json($data);

        }

        public function integrantes_grupo(Request $request){

            // Buscar los integrantes del grupo
            $secciones = app('db')->select("    SELECT 
                                                    DISTINCT(CODAREA) AS CODAREA
                                                FROM RH_EMPLEADOS T1
                                                INNER JOIN RRHH_IND_INT_GRUPO T2
                                                ON T1.NIT = T2.ID_PERSONA
                                                AND T2.ID_GRUPO = $request->id_grupo");

            foreach ($secciones as &$seccion) {

                $area = Area::find($seccion->codarea);

                $seccion->nombre = $area->descripcion;
                $seccion->expand = false;
                $seccion->check = false;

                /* Buscar los integrantes de la sección y del grupo */

                $integrantes = app('db')->select("  SELECT 
                                                        CONCAT(T1.NOMBRE, CONCAT(' ', T1.APELLIDO)) AS NOMBRE, 
                                                        T1.NIT
                                                    FROM RH_EMPLEADOS T1
                                                    INNER JOIN RRHH_IND_INT_GRUPO T2
                                                    ON T1.NIT = T2.ID_PERSONA
                                                    AND T2.ID_GRUPO = $request->id_grupo
                                                    AND T1.CODAREA = $seccion->codarea");
                
                foreach ($integrantes as &$integrante) {
                        
                    $integrante->check = false;

                }

                $seccion->integrantes = $integrantes;

            }

            return response()->json($secciones);

        }

        public function actividades_grupo(Request $request){

            return response()->json($request);

        }

    }

?>