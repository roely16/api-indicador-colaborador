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

    use App\PeriodoEvaCompetencia;

    class CompetenciaController extends Controller{

        public function obtener_perfil(Request $request){

            $empleado = Empleado::where('nit', $request->nit)->first();

            $perfil = Perfil::find($empleado->id_perfil);

            $tipos_competencias = TipoCompetencia::all();

            if ($perfil) {
                
                foreach ($tipos_competencias as &$tipo) {
                
                    $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $perfil->id)->where('deleted_at', null)->get();
    
                    foreach ($competencias as &$competencia) {
                        
                        $competencia->resultado = null;
    
                    }
    
                    $tipo->competencias = $competencias;
    
                }
    
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

                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        T1.PERIODO,
                                                        T1.COMPETENCIAS_TECNICAS, 
                                                        T1.COMPETENCIAS_BLANDAS,
                                                        T1.CALIFICACION,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT,
                                                        T3.DESCRIPCION AS AREA,
                                                        T2.CODAREA
                                                    FROM RRHH_IND_EVA_COMPETENCIA T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    INNER JOIN RH_AREAS T3
                                                    ON T2.CODAREA = T3.CODAREA
                                                    ORDER BY T1.ID DESC");

                $headers = [
                    [
                        "text" => "Colaborador",
                        "value" => "colaborador",
                        "width" => "30%"
                    ],
                    [
                        "text" => "Sección",
                        "value" => "area",
                        "width" => "20%"
                    ],
                    [
                        "text" => "Fecha de Registro",
                        "value" => "created_at",
                        "width" => "20%"
                    ],
                    [
                        "text" => "Mes",
                        "value" => "periodo",
                        "width" => "10%"
                    ],
                    [
                        "text" => "Calificación",
                        "value" => "calificacion",
                        "width" => "10%",
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


            }else{

                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        T1.PERIODO,
                                                        T1.COMPETENCIAS_TECNICAS, 
                                                        T1.COMPETENCIAS_BLANDAS,
                                                        T1.CALIFICACION,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT,
                                                        T2.CODAREA
                                                    FROM RRHH_IND_EVA_COMPETENCIA T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T2.CODAREA = $request->codarea
                                                    ORDER BY T1.ID DESC");

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

            $data = [
                "items" => $evaluaciones,
                "headers" => $headers,
            ];

            return response()->json($data);

        }

        public function obtener_periodos(Request $request){

            //$periodos = PeriodoEvaCompetencia::all();

            $periodos = app('db')->select(" SELECT
                                                ID,
                                                TO_CHAR(FECHA_INICIO, 'DD/MM/YYYY') AS FECHA_INICIO,
                                                TO_CHAR(FECHA_FIN, 'DD/MM/YYYY') AS FECHA_FIN,
                                                TO_CHAR(FECHA_INICIO, 'YYYY-MM-DD') AS FECHA_INICIO_FORMAT,
                                                TO_CHAR(FECHA_FIN, 'YYYY-MM-DD') AS FECHA_FIN_FORMAT,
                                                OBSERVACION
                                            FROM RRHH_IND_EVA_COMP_PERIODO
                                            WHERE DELETED_AT IS NULL
                                            ORDER BY ID DESC");

            $i = 1;

            foreach ($periodos as &$periodo) {
            
                $periodo->index = $i;
                
                $i++;

            }

            $headers = [
                [
                    "text" => "Observación",
                    "value" => "observacion",
                    "width" => "45%"
                ],
                [
                    "text" => "Inicio",
                    "value" => "fecha_inicio",
                    "width" => "20%"
                ],
                [
                    "text" => "Fin",
                    "value" => "fecha_fin",
                    "width" => "20%"
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "sortable" => false,
                    "align" => "right",
                    "width" => "15%"
                ]
            ];


            $data = [
                "items" => $periodos,
                "headers" => $headers
            ];

            return response()->json($data);

        }

        public function registrar_periodo(Request $request){

            $periodo = new PeriodoEvaCompetencia();

            $periodo->observacion = $request->observacion;
            $periodo->fecha_inicio = $request->fecha_inicio;
            $periodo->fecha_fin = $request->fecha_fin;
            $result = $periodo->save();

            if ($result) {

                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "El periodo a sido registrado exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function eliminar_periodo(Request $request){

            $periodo = PeriodoEvaCompetencia::find($request->id);
            $result = $periodo->delete();

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "El periodo a sido eliminado exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function editar_periodo(Request $request){

            $periodo = PeriodoEvaCompetencia::find($request->id);

            $periodo->observacion = $request->observacion;
            $periodo->fecha_inicio = $request->fecha_inicio;
            $periodo->fecha_fin = $request->fecha_fin;
            $result = $periodo->save();

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "El periodo a sido actualizado exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

    }