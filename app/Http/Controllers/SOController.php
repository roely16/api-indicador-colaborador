<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Grupo;
    use App\Integrante;
    use App\Area;
    use App\Empleado;
    use App\Actividad;
    use App\ActividadResponsable;

    class SOController extends Controller{

        public function registrar_grupo(Request $request){

            $grupo = new Grupo();

            $grupo->nombre = $request->nombre;
            $grupo->save();

            $data = [
                "status" => 200,
                "title" => "Excelente!",
                "message" => "El grupo a sido creado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

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

            $id_actividad = $request->id_actividad ? $request->id_actividad : '';

            // Buscar los integrantes del grupo
            $secciones = app('db')->select("    SELECT 
                                                    DISTINCT(CODAREA) AS CODAREA
                                                FROM RH_EMPLEADOS T1
                                                INNER JOIN RRHH_IND_INT_GRUPO T2
                                                ON T1.NIT = T2.ID_PERSONA
                                                AND T2.ID_GRUPO = $request->id_grupo
                                                AND T1.NIT NOT IN (
                                                    SELECT ID_PERSONA
                                                    FROM RRHH_IND_ACTIVIDAD_RESPONSABLE
                                                    WHERE ID_ACTIVIDAD = '$id_actividad'
                                                )");

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
                                                    AND T1.CODAREA = $seccion->codarea
                                                    AND T1.NIT NOT IN (
                                                        SELECT ID_PERSONA
                                                        FROM RRHH_IND_ACTIVIDAD_RESPONSABLE
                                                        WHERE ID_ACTIVIDAD = '$id_actividad'
                                                    )");
                
                foreach ($integrantes as &$integrante) {
                        
                    $integrante->check = false;

                }

                $seccion->integrantes = $integrantes;

            }

            return response()->json($secciones);

        }

        public function actividades_grupo(Request $request){

            $actividades = app('db')->select("  SELECT 
                                                    ID, 
                                                    NOMBRE, TO_CHAR(FECHA_CUMPLIMIENTO, 'DD/MM/YYYY') AS FECHA_CUMPLIMIENTO, ID_GRUPO
                                                FROM RRHH_IND_ACTIVIDAD
                                                WHERE ID_GRUPO = $request->id_grupo");

            //$actividades = Actividad::where('id_grupo', $request->id_grupo)->get();

            foreach ($actividades as &$actividad) {

                // Obtener las persona asignadas a la actividad
                $responsables = app('db')->select(" SELECT T2.*, T1.CUMPLIO
                                                    FROM RRHH_IND_ACTIVIDAD_RESPONSABLE T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE ID_ACTIVIDAD = $actividad->id");
                
                $actividad->responsables = $responsables;

                $actividad->check = false;
                $actividad->calificar = true;
                $actividad->expand = false;

            }

            return response()->json($actividades);

        }

        public function registrar_actividad(Request $request){
            
            $actividad = new Actividad();

            $actividad->nombre = $request->nombre;
            $actividad->fecha_cumplimiento = $request->fecha;
            $actividad->id_grupo = $request->id_grupo;
            $actividad->save();

            return response()->json($actividad);

        }
        
        public function asignar_actividad(Request $request){
            
            foreach ($request->personas as $persona) {
                
                $actividad_responsable = new ActividadResponsable();
                $actividad_responsable->id_actividad = $request->id_actividad;
                $actividad_responsable->id_persona = $persona["nit"];
                $actividad_responsable->save();

            }

            return response()->json($request);

        }

        public function calificar_responsable(Request $request){

            $result = app('db')->table('RRHH_IND_ACTIVIDAD_RESPONSABLE')->where('id_actividad', $request->id_actividad)->where('id_persona', $request->nit)->update(['cumplio' => $request->cumplio]);

            return response()->json($result);

        }

        public function detalle_grupo(Request $request){

            $grupo = Grupo::find($request->id_grupo);

            return response()->json($grupo);

        }

        public function editar_grupo(Request $request){

            $grupo = Grupo::find($request->id);

            $grupo->nombre = $request->nombre;
            $grupo->save();

            $data = [
                "status" => 200,
                "title" => "Excelente!",
                "message" => "El grupo a sido actualizado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }
    }

?>