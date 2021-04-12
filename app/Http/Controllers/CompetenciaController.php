<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\TipoCompetencia;
    use App\Competencia;
    use App\Empleado;
    use App\Perfil;

    use App\EvaluacionCompetencia;
    use App\DetalleEvaluacionCompetencia;

    use App\Criterio;
    use App\Menu;
    use App\Permiso;

    class CompetenciaController extends Controller{

        public function obtener_perfil(Request $request){

            $empleado = Empleado::where('nit', $request->nit)->first();

            $perfil = Perfil::find($empleado->id_perfil);

            $tipos_competencias = TipoCompetencia::all();

            foreach ($tipos_competencias as &$tipo) {
                
                $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $empleado->id_perfil)->get();

                foreach ($competencias as &$competencia) {
                    
                    $competencia->resultado = 1;

                }

                $tipo->competencias = $competencias;

            }

            $data = [
                "empleado" => $empleado,
                "tipos_competencias" => $tipos_competencias,
                "perfil" => $perfil
            ];

            return response()->json($data);

        }

        public function registrar_evaluacion(Request $request){

            $res_competencias = [];

            foreach ($request->tipos_competencias as $tipo) {
                
                $res_competencias [] = $tipo["result"];

            }

            $evaluacion = new EvaluacionCompetencia();

            $evaluacion->id_persona = $request->nit_colaborador;
            $evaluacion->observaciones = $request->observaciones;
            $evaluacion->competencias_tecnicas = $res_competencias[0];
            $evaluacion->competencias_blandas = $res_competencias[1];
            $evaluacion->periodo = $request->month;
            $evaluacion->calificacion = $request->total;
            
            $evaluacion->save();

            foreach ($request->tipos_competencias as $tipo) {
                
                foreach ($tipo["competencias"] as $competencia) {
                    
                    $detalle_evaluacion = new DetalleEvaluacionCompetencia();
                    $detalle_evaluacion->id_evaluacion = $evaluacion->id;
                    $detalle_evaluacion->id_competencia = $competencia["id"];
                    $detalle_evaluacion->resultado = $competencia["resultado"];

                    $detalle_evaluacion->save();

                }

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "La evaluación a sido registrada exitosamente",
                "type" => "success"
            ];


            return response()->json($data);

        }

        public function detalle_evaluacion(Request $request){

            $evaluacion = EvaluacionCompetencia::find($request->id);

            $empleado = Empleado::where('nit', $request->nit_colaborador)->first();

            $perfil = Perfil::find($empleado->id_perfil);

            $tipos_competencias = TipoCompetencia::all();

            foreach ($tipos_competencias as &$tipo) {

                $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $empleado->id_perfil)->get();

                foreach ($competencias as &$competencia) {
                    
                    // Buscar el resultado obtenido
                    $detalle = DetalleEvaluacionCompetencia::where('id_evaluacion', $evaluacion->id)->where('id_competencia', $competencia->id)->first();

                    if ($detalle) {
                        
                        $competencia->resultado = intval($detalle->resultado);

                    }

                }

                $tipo->competencias = $competencias;


            }

            $evaluacion->tipos_competencias = $tipos_competencias;
            $evaluacion->perfil = $perfil->nombre;

            return response()->json($evaluacion);

        }

        public function editar_evaluacion(Request $request){

            $evaluacion = EvaluacionCompetencia::find($request->id_evaluacion);

            $res_competencias = [];

            foreach ($request->tipos_competencias as $tipo) {
                
                $res_competencias [] = $tipo["result"];

            }

            $evaluacion->observaciones = $request->observaciones;
            $evaluacion->competencias_tecnicas = $res_competencias[0];
            $evaluacion->competencias_blandas = $res_competencias[1];
            $evaluacion->periodo = $request->month;
            $evaluacion->calificacion = $request->total;

            $evaluacion->save();

            foreach ($request->tipos_competencias as $tipo) {
                
                foreach ($tipo["competencias"] as $competencia) {
                    
                    $detalle_evaluacion = DetalleEvaluacionCompetencia::where('id_evaluacion', $request->id_evaluacion)->where('id_competencia', $competencia["id"])->first();

                    $detalle_evaluacion->resultado = $competencia["resultado"];

                    $detalle_evaluacion->save();

                }

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "La evaluación a sido actualizada exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function eliminar_evaluacion(Request $request){

            $evaluacion = EvaluacionCompetencia::find($request->id);

            $result = $evaluacion->delete();

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "La evaluación a sido eliminada exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function obtener_evaluaciones(Request $request){

            $criterio = Criterio::where('modulo', $request->url)->first();
            
            // Dependiendo del permiso mostrar todas las secciones o no
            $menu = Menu::where('url', $request->url)->first();

            $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu', $menu->id)->first();

            if ($permiso->secciones == 'S') {



            }else{

                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        T1.PERIODO,
                                                        T1.COMPETENCIAS_TECNICAS, 
                                                        T1.COMPETENCIAS_BLANDAS,
                                                        T1.CALIFICACION,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT
                                                    FROM RRHH_IND_EVA_COMPETENCIA T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T2.CODAREA = $request->codarea
                                                    ORDER BY T1.ID DESC");

            }

            foreach ($evaluaciones as $evaluacion) {
                
                $evaluacion->calificacion = round($evaluacion->calificacion, 2);

                if ($evaluacion->calificacion >= 0 && $evaluacion->calificacion < 60) {
                   
                    $evaluacion->color = 'red';

                }elseif( $evaluacion->calificacion >= 60 && $evaluacion->calificacion < 80){

                    $evaluacion->color = 'orange';

                }else{

                    $evaluacion->color = 'green';

                }

            }

            $headers = [
                [
                    "text" => "Colaborador",
                    "value" => "colaborador",
                    "width" => "35%"
                ],
                [
                    "text" => "Fecha de Registro",
                    "value" => "created_at",
                    "width" => "25%"
                ],
                [
                    "text" => "Mes",
                    "value" => "periodo",
                    "width" => "10%"
                ],
                [
                    "text" => "Calificación",
                    "value" => "calificacion",
                    "width" => "20%",
                    "align" => "center",
                    "sortable" => false
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "sortable" => false,
                    "align" => "right",
                    "width" => "10%"
                ]
            ];

            $data = [
                "items" => $evaluaciones,
                "headers" => $headers,
            ];

            return response()->json($data);

        }

    }