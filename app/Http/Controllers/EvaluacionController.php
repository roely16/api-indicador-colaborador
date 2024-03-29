<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\Evaluacion;
    use App\DetalleEvaluacion;
    use App\Menu;
    use App\Permiso;

    class EvaluacionController extends Controller{

        public function registrar_evaluacion(Request $request){

            /*
                TODO 
                - Validar que registrar SNC, Comentarios, Correcciones, Motivos
            */

            $req_criterio = (object) $request->criterio;

            $evaluacion = new Evaluacion();
            $evaluacion->id_criterio = $req_criterio->id;
            $evaluacion->id_persona = $request->id_persona;
            $evaluacion->valor_criterio = $req_criterio->valor;
            $evaluacion->mes = $request->month;

            $evaluacion->calificacion = $request->calificacion;

            $evaluacion->save();

            $criterios = $request->items;

            foreach ($criterios as &$criterio) {
                
                $criterio = (object) $criterio;

                $detalle_evaluacion = new DetalleEvaluacion();
                $detalle_evaluacion->id_evaluacion = $evaluacion->id;
                $detalle_evaluacion->id_item = $criterio->id;
                $detalle_evaluacion->valor = $criterio->valor;

                
                if (array_key_exists('comentario', $criterio)) {

                    $detalle_evaluacion->comentario = $criterio->comentario;

                }
                
                if (array_key_exists('data_calculo', $criterio)) {

                    if ($criterio->data_calculo != null) {
                    
                        $data_calculo = (object) $criterio->data_calculo;

                        $detalle_evaluacion->operados = $data_calculo->operados;

                        // Si existen SNC
                        if (array_key_exists('snc', $data_calculo)) {

                            $detalle_evaluacion->snc = $data_calculo->snc;

                        }

                        // Si existen correcciones
                        if (array_key_exists('correcciones', $data_calculo)) {
                            
                            $detalle_evaluacion->correcciones = $data_calculo->correcciones;
                            
                        }

                    }

                }

                if (array_key_exists('motivos', $criterio)) {

                    // Validar que existan motivos
                    if (count($criterio->motivos) > 0) {
                        
                        $str_motivos = null;

                        foreach ($criterio->motivos as $motivo) {
                            
                            $motivo = (object) $motivo;

                            $str_motivos = $str_motivos . $motivo->descripcion . " \r\n";

                        }

                        $detalle_evaluacion->motivo = $str_motivos;

                    }else{

                        $detalle_evaluacion->motivo = null;

                    }

                }
                
                if ($req_criterio->division == 'S') {
                    
                    $detalle_evaluacion->calificacion = ($criterio->valor * $criterio->calificacion) / 100;

                }else {

                    $detalle_evaluacion->calificacion = $criterio->valor * $criterio->calificacion;

                }
                
                $detalle_evaluacion->save();

            }

            $data = [
                "title" => "Excelente",
                "message" => "La evaluación a sido registrada exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function obtener_evaluaciones(Request $request){

            $criterio = Criterio::where('modulo', $request->url)->first();
            
            // Dependiendo del permiso mostrar todas las secciones o no
            $menu = Menu::where('url', $request->url)->first();

            $permiso = Permiso::where('id_persona', $request->nit)->where('id_menu', $menu->id)->first();

            if ($permiso->secciones == 'S') {
                
                $procesos = implode(",", $request->areas);

                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        T1.MES,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_aT,
                                                        T1.VALOR_CRITERIO,
                                                        T3.DESCRIPCION AS AREA,
                                                        T3.CODAREA, 
                                                        T1.CALIFICACION
                                                    FROM RRHH_IND_EVALUACION T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    INNER JOIN RH_AREAS T3
                                                    ON T2.CODAREA = T3.CODAREA
                                                    WHERE T1.ID_CRITERIO = $criterio->id
                                                    AND T1.MES = '$request->date'
                                                    AND T3.CODAREA IN ($procesos)
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
                        "value" => "mes",
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

                // Buscar solo las evaluaciones de la sección del usuario
                $evaluaciones = app('db')->select(" SELECT 
                                                        T1.ID, 
                                                        T1.ID_PERSONA,
                                                        T1.MES,
                                                        CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS COLABORADOR, 
                                                        TO_CHAR(T1.CREATED_AT, 'DD/MM/YYYY HH24:MI:SS') AS CREATED_AT,
                                                        T1.VALOR_CRITERIO,
                                                        T2.CODAREA,
                                                        T1.CALIFICACION
                                                    FROM RRHH_IND_EVALUACION T1
                                                    INNER JOIN RH_EMPLEADOS T2
                                                    ON T1.ID_PERSONA = T2.NIT
                                                    WHERE T1.ID_CRITERIO = $criterio->id
                                                    AND T2.CODAREA = $request->codarea
                                                    AND T1.MES = '$request->date'
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
                        "value" => "mes",
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
                                
                // Formato del mes y año
                //$evaluacion->mes = date('F Y', strtotime($evaluacion->mes));

                // Obtener el detalle de la evaluación
                $detalle = DetalleEvaluacion::where('id_evaluacion', $evaluacion->id)->get();

                $total = 0;

                foreach ($detalle as $item) {
                    
                    $total += $item->calificacion;

                }

                //$evaluacion->calificacion = round(($total / $evaluacion->valor_criterio) * 100, 2);

                $evaluacion->calificacion = $evaluacion->calificacion > 100 ? 100 : $evaluacion->calificacion;

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
                "criterio" => $criterio
            ];

            return response()->json($data);

        }

        public function eliminar_evaluacion(Request $request){

            $evaluacion = Evaluacion::find($request->id);
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

        public function editar_evaluacion(Request $request){

            $req_criterio = (object) $request->criterio;

            $evaluacion = Evaluacion::find($request->id_evaluacion);
            $evaluacion->mes = $request->month;
            $evaluacion->calificacion = $request->calificacion;
            $evaluacion->save();

            // Eliminar los registros anteriores de la evaluación
            foreach ($request->items as $item) {
                
                $item = (object) $item;

                $calificacion = 0;

                if ($req_criterio->division == 'S') {

                    $calificacion = ($item->valor * $item->calificacion) / 100;

                }else{

                    $calificacion = $item->valor * $item->calificacion;

                }

                if ($item->comentario) {

                    $result = app('db')->table('rrhh_ind_evaluacion_detalle')->where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->update(['calificacion' => $calificacion, 'comentario' => $item->comentario, 'valor' => $item->valor]);

                }else{

                    $result = app('db')->table('rrhh_ind_evaluacion_detalle')->where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->update(['calificacion' => $calificacion, 'valor' => $item->valor]);

                }


                    // Validar que existan motivos
                    if (isset($item->motivos)) {
                        
                        $str_motivos = null;

                        foreach ($item->motivos as $motivo) {
                            
                            $motivo = (object) $motivo;

                            $str_motivos = $str_motivos . $motivo->descripcion . " \r\n";

                        }

                        $result = app('db')->table('rrhh_ind_evaluacion_detalle')->where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->update(['motivo' => $str_motivos]);

                    }else{

                        $result = app('db')->table('rrhh_ind_evaluacion_detalle')->where('id_evaluacion', $request->id_evaluacion)->where('id_item', $item->id)->update(['motivo' => null]);

                    }

        

                if (isset($item->data_calculo)) {

                    $data_calculo = (object) $item->data_calculo;

                    try {

                        // Si existen SNC
                        if ($data_calculo->snc) {

                            $result = app('db')
                                        ->table('rrhh_ind_evaluacion_detalle')
                                        ->where('id_evaluacion', $request->id_evaluacion)
                                        ->where('id_item', $item->id)
                                        ->update([
                                            'operados' => $data_calculo->operados,
                                            'snc' => $data_calculo->snc
                                        ]);

                        }

                        // Si existen correcciones
                        
                        if ($data_calculo->correcciones) {
                        
                            $result = app('db')
                                        ->table('rrhh_ind_evaluacion_detalle')
                                        ->where('id_evaluacion', $request->id_evaluacion)
                                        ->where('id_item', $item->id)
                                        ->update([
                                            'operados' => $data_calculo->operados,
                                            'correcciones' => $data_calculo->correcciones
                                        ]);
                            
                        }

                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    

                }


            }

            if ($result) {
                
                $data = [
                    "status" => 200,
                    "title" => "Excelente",
                    "message" => "La evaluación a sido actualizada exitosamente",
                    "type" => "success"
                ];

            }else{

                $data = [
                    "status" => 200
                ];

            }

            return response()->json($data);

        }

    }

?>