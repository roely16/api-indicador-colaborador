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

    use App\SeguimientoCompetencias;
    use App\ActividadSeguimiento;
    use App\ArchivoActividad;

    class CompetenciaController extends Controller{

        public function obtener_perfil(Request $request){

            $empleado = Empleado::where('nit', $request->nit)->first();

            /* Obtener los perfiles del colaborador */
            $perfil = app('db')->select("   SELECT T2.*
                                            FROM RH_EMPLEADO_PERFIL T1 
                                            INNER JOIN RRHH_PERFIL T2
                                            ON T1.ID_PERFIL = T2.ID
                                            WHERE T1.NIT = '$request->nit'");
            
            $data = [
                "empleado" => $empleado,
                "perfil" => $perfil
            ];

            return response()->json($data);

        }

        public function detalle_perfil_colaborador(Request $request){

            $tipos_competencias = TipoCompetencia::all();
            
            foreach ($tipos_competencias as &$tipo) {
            
                $competencias = Competencia::where('id_tipo', $tipo->id)
                                ->where('id_perfil', $request->id_perfil)
                                ->where('deleted_at', null)
                                ->get();

                foreach ($competencias as &$competencia) {
                    
                    $competencia->resultado = null;

                }

                $tipo->competencias = $competencias;

            }

            return response()->json($tipos_competencias);

        }

        public function registrar_evaluacion(Request $request){

            $res_competencias = [];

            foreach ($request->tipos_competencias as $tipo) {
                
                $res_competencias [] = $tipo["result"];

            }

            /*
                Obtener el ID del periodo de evaluación en curso
            */

            $today = date('d/m/Y');

            $result = app('db')->select("   SELECT 
                                                ID
                                            FROM RRHH_IND_EVA_COMP_PERIODO
                                            WHERE TO_DATE('$today', 'DD/MM/YYYY') BETWEEN FECHA_INICIO
                                            AND FECHA_FIN");

            $id_periodo = $result ? $result[0]->id : null;

            $evaluacion = new EvaluacionCompetencia();

            $evaluacion->id_persona = $request->nit_colaborador;
            $evaluacion->observaciones = $request->observaciones;
            $evaluacion->competencias_tecnicas = $res_competencias[0];
            $evaluacion->competencias_blandas = $res_competencias[1];
            $evaluacion->periodo = $request->month;
            $evaluacion->calificacion = $request->total;
            $evaluacion->id_periodo = $id_periodo;
            $evaluacion->id_perfil = $request->id_perfil;
            
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

            /* 

                Si el total de la nota es menor o igual a 69

            */

            if ($request->total <= 69) {
                
                /*
                    Registrar las competencias con puntaje menor o igual a 3
                */

                foreach ($request->tipos_competencias as $tipo) {
                
                    foreach ($tipo["competencias"] as $competencia) {


                        if ($competencia["resultado"] <= 3) {
                            
                            $seguimiento = new SeguimientoCompetencias();

                            $seguimiento->id_evaluacion = $evaluacion->id;
                            $seguimiento->id_competencia = $competencia["id"];
                            $seguimiento->resultado = $competencia["resultado"];
                            $seguimiento->meta = 4;
                            $seguimiento->tipo = $competencia["resultado"] <= 2 ? 'C' : 'P';
                            $seguimiento->save();

                        }

                    }

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

            $perfil = Perfil::find($evaluacion->id_perfil);

            /* Obtener todos los perfiles del colaborador */
            $perfiles = app('db')->select(" SELECT T2.*
                                            FROM RH_EMPLEADO_PERFIL T1
                                            INNER JOIN RRHH_PERFIL T2
                                            ON T1.ID_PERFIL = T2.ID
                                            WHERE T1.NIT = '$request->nit_colaborador'");

            $tipos_competencias = TipoCompetencia::all();

            foreach ($tipos_competencias as &$tipo) {

                $competencias = Competencia::where('id_tipo', $tipo->id)->where('id_perfil', $evaluacion->id_perfil)->get();

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
            $evaluacion->perfil = $perfil ? $perfil->id : null;
            $evaluacion->nombre_perfil = $perfil ? $perfil->nombre : null;
            $evaluacion->perfiles = $perfiles;

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
                                                        T2.CODAREA,
                                                        T1.ID_PERFIL
                                                    FROM RRHH_IND_EVA_COMPETENCIA T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    INNER JOIN RH_AREAS T3
                                                    ON T2.CODAREA = T3.CODAREA
                                                    WHERE T1.POSPONER IS NULL
                                                    ORDER BY T1.ID DESC");

                foreach ($evaluaciones as &$evaluacion) {
                                    
                    $perfil = Perfil::find($evaluacion->id_perfil);

                    $evaluacion->perfil = $perfil ? $perfil->nombre : null;

                }

                $headers = [
                    [
                        "text" => "Colaborador",
                        "value" => "colaborador",
                        "width" => "15%"
                    ],
                    [
                        "text" => "Perfil",
                        "value" => "perfil",
                        "width" => "20%"
                    ],
                    [
                        "text" => "Sección",
                        "value" => "area",
                        "width" => "15%"
                    ],
                    [
                        "text" => "Fecha de Registro",
                        "value" => "created_at",
                        "width" => "15%"
                    ],
                    [
                        "text" => "Mes",
                        "value" => "periodo",
                        "width" => "10%"
                    ],
                    [
                        "text" => "Calificación",
                        "value" => "calificacion",
                        "width" => "5%",
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
                                                        T2.CODAREA,
                                                        T1.ID_PERFIL
                                                    FROM RRHH_IND_EVA_COMPETENCIA T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T2.CODAREA = $request->codarea
                                                    AND T1.POSPONER IS NULL
                                                    ORDER BY T1.ID DESC");

                foreach ($evaluaciones as &$evaluacion) {
                    
                    $perfil = Perfil::find($evaluacion->id_perfil);

                    $evaluacion->perfil = $perfil ? $perfil->nombre : null;

                }

                $headers = [
                    [
                        "text" => "Colaborador",
                        "value" => "colaborador",
                        "width" => "25%"
                    ],
                    [
                        "text" => "Perfil",
                        "value" => "perfil",
                        "width" => "20%"
                    ],
                    [
                        "text" => "Fecha de Registro",
                        "value" => "created_at",
                        "width" => "15%"
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
                        "width" => "15%"
                    ]
                ];


            }

            foreach ($evaluaciones as &$evaluacion) {
                
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

            return response()->json($request);

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

            $notificar = $request->notificar;

            if ($notificar) {
                
                // Notificar a los asesores de la creación del periodo de evaluaciòn

            }

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

        public function posponer_evaluacion(Request $request){

            $evaluacion = new EvaluacionCompetencia();
            $evaluacion->id_persona = $request->nit_colaborador;
            $evaluacion->periodo = $request->month;
            $evaluacion->posponer = 'S';
            $result = $evaluacion->save();

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "La solicitud a sido realizada exitosamente",
                    "type" => "success"
                ];

            }

            return response()->json($data);

        }

        public function obtener_seguimiento(Request $request){

            $seguimiento = app('db')->select("  SELECT 
                                                    T1.ID, 
                                                    T2.NOMBRE AS COMPETENCIA, 
                                                    T1.RESULTADO, 
                                                    T1.META, 
                                                    T1.TIPO
                                                FROM RRHH_IND_EVA_COMP_SEGUIMIENTO T1
                                                INNER JOIN RRHH_COMPETENCIA T2
                                                ON T1.ID_COMPETENCIA = T2.ID
                                                WHERE ID_EVALUACION = $request->id");

            /*
                Obtener las actividades registradas por cada seguimiento
            */

            foreach ($seguimiento as $item) {
                
                $actividades = app('db')->select("  SELECT 
                                                        ID,
                                                        ID_SEGUIMIENTO, 
                                                        DESCRIPCION, 
                                                        TO_CHAR(FECHA_INICIO, 'DD/MM/YYYY') AS FECHA_INICIO,
                                                        TO_CHAR(FECHA_FIN, 'DD/MM/YYYY') AS FECHA_FIN,
                                                        OBSERVACIONES,
                                                        CUMPLIO
                                                    FROM RRHH_IND_EVA_COMP_SEG_ACT
                                                    WHERE ID_SEGUIMIENTO = $item->id");

                $item->actividades = $actividades;

            }

            $headers = [
                [
                    "text" => "Competencia",
                    "value" => "competencia"
                ],
                [
                    "text" => "Resultado",
                    "value" => "resultado"
                ],
                [
                    "text" => "Meta",
                    "value" => "meta"
                ],
                [
                    "text" => "Tipo",
                    "value" => "tipo"
                ],
                [
                    "text" => "Acciones",
                    "value" => "data-table-expand",
                    "sortable" => false,
                    "align" => "right"
                ]
            ];

            $data = [
                "items" => $seguimiento,
                "headers" => $headers
            ];

            return response()->json($data);

        }

        public function registrar_actividad(Request $request){

            $actividad = new ActividadSeguimiento();
            
            $actividad->id_seguimiento = $request->id_seguimiento;
            $actividad->descripcion = $request->descripcion;
            $actividad->fecha_inicio = $request->inicio;
            $actividad->fecha_fin = $request->fin;
            $actividad->observaciones = $request->observaciones;
            $result = $actividad->save();

            if (!$result) {
                
                $data = [
                    "status" => 100
                ];

                return response()->json($data);
            }

            $data = [
                "status" => 200,
                "data" => $actividad
            ];

            return response()->json($data);

        }

        public function subir_archivos_actividad(Request $request){

            $nombre = $request->file('file')->getClientOriginalName();
            $identificador = uniqid() . '.' . $request->file('file')->extension();

            if($request->file('file')->move('archivos', $identificador)){

                /*
                    Registrar en BD
                */

                $archivo = new ArchivoActividad();

                $archivo->id_actividad = $request->id_actividad;
                $archivo->nombre= $nombre;
                $archivo->identificador = $identificador;
                $result = $archivo->save();

                if ($result) {
                    
                    $archivo->path = "/archivos/" . $archivo->identificador;
                    $archivo->select = false;

                    if(exif_imagetype("archivos/" . $archivo->identificador)) {
                               
                        $archivo->image = true;
                        
                    }else{

                        $archivo->image = false;

                    }

                }

            }

            return response()->json($archivo);

        }

        public function detalle_actividad(Request $request){

            try {
                
                $actividad = app('db')->select("    SELECT 
                                                        ID, 
                                                        ID_SEGUIMIENTO,
                                                        DESCRIPCION, 
                                                        TO_CHAR(FECHA_INICIO, 'YYYY-MM-DD') AS INICIO,
                                                        TO_CHAR(FECHA_FIN, 'YYYY-MM-DD') AS FIN,
                                                        OBSERVACIONES
                                                    FROM RRHH_IND_EVA_COMP_SEG_ACT
                                                    WHERE ID = $request->id");

                if ($actividad) {
                    
                    $actividad = $actividad[0];

                    $actividad->files = [];

                    /*
                        Buscar los archivos
                    */

                    $actividad->archivos = app('db')->select("  SELECT *
                                                                FROM RRHH_IND_EVA_COMP_ARCHIVO
                                                                WHERE ID_ACTIVIDAD = $actividad->id
                                                                ORDER BY ID ASC");

                    if ($actividad->archivos) {
                        
                        foreach ($actividad->archivos as $archivo) {
                            
                            if(exif_imagetype("archivos/" . $archivo->identificador)) {
                               
                                $archivo->image = true;
                                
                            }else{

                                $archivo->image = false;

                            }

                            $archivo->path = "archivos/" . $archivo->identificador;
                            $archivo->select = false;

                        }

                    }
                }

            } catch (\Exception $e) {
                
                return response()->json($e->getMessage());

            }
            

            return response()->json($actividad);

        }

        public function editar_actividad(Request $request){

            $actividad = ActividadSeguimiento::find($request->id);

            $actividad->descripcion = $request->descripcion;
            $actividad->fecha_inicio = $request->inicio;
            $actividad->fecha_fin = $request->fin;
            $actividad->observaciones = $request->observaciones;
            $result = $actividad->save();
            
            if (!$result) {
                
                $data = [
                    "status" => 100
                ];

                return response()->json($data);
            }

            $data = [
                "status" => 200,
                "data" => $actividad
            ];

            return response()->json($data);

        }

        public function eliminar_archivos(Request $request){

            $archivos = $request->archivos;

            foreach ($archivos as $archivo) {
                
                $archivo_ = ArchivoActividad::find($archivo);
                
                unlink('archivos/'.$archivo_->identificador);

                $archivo_->delete();

            }

            return response()->json($archivos);


        }

        public function eliminar_actividad(Request $request){

            $actividad = ActividadSeguimiento::find($request->id);

            /*
                Validar si la actividad tiene archivos adjuntos
            */

            $archivos = ArchivoActividad::where('id_actividad', $actividad->id)->get();

            if ($archivos) {
                
                foreach ($archivos as $archivo) {
                    
                    $archivo_ = ArchivoActividad::find($archivo->id);
                
                    unlink('archivos/'.$archivo_->identificador);

                    $archivo_->delete();

                }

            }

            $actividad->delete();

            return response()->json($actividad);

        }

        public function corregir_evaluaciones(Request $request){

            $evaluaciones = EvaluacionCompetencia::where('id_perfil', null)->get();

            foreach ($evaluaciones as $evaluacion) {
                
                $perfil = app('db')->select("   SELECT ID_PERFIL
                                                FROM RH_EMPLEADO_PERFIL
                                                WHERE NIT = '$evaluacion->id_persona'");

                if ($perfil) {
                    
                    $perfil = $perfil[0];

                    $evaluacion->id_perfil = $perfil->id_perfil;

                }else{

                    /* Buscar a nivel de Empleado */

                    $empleado = Empleado::where('nit', $evaluacion->id_persona)->first();

                    if ($empleado) {
                        
                        $evaluacion->id_perfil = $empleado->id_perfil;

                    }

                }

                if ($evaluacion->id_perfil) {
                    
                    /* Actualizar */

                    $result = app('db')
                                ->table('RRHH_IND_EVA_COMPETENCIA')
                                ->where('id', $evaluacion->id)
                                ->update([
                                    'id_perfil' => $evaluacion->id_perfil
                                ]);

                }

            }

            return response()->json($evaluaciones);

        }

        public function corregir_seguimiento(Request $request){
            
            $seguimiento = [];

            $evaluaciones = EvaluacionCompetencia::where('calificacion', '<=', 69)->get();

            foreach ($evaluaciones as $evaluacion) {
                
                $detalle = DetalleEvaluacionCompetencia::where('id_evaluacion', $evaluacion->id)->where('resultado', '<=', 3)->get();

                foreach ($detalle as $item) {
                    
                    $registro = [];

                    $registro["id_evaluacion"] = $item->id_evaluacion;
                    $registro["id_competencia"] = $item->id_competencia;
                    $registro["resultado"] = $item->resultado;
                    $registro["meta"] = 4;
                    $registro["tipo"] = $item->resultado == 3 ? 'P' : 'C';

                    $seguimiento [] = $registro;

                }

                $evaluacion->detalle = $detalle;

            }

            $result = app('db')->table('RRHH_IND_EVA_COMP_SEGUIMIENTO')->insert($seguimiento);

            return response()->json($seguimiento);

        }

        public function cumplimiento_actividad(Request $request){

            $actividad = ActividadSeguimiento::find($request->id);

            $actividad->cumplio = $request->cumplio;
            $actividad->save();

            return response()->json($actividad);

        }

    }