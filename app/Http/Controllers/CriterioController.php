<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Criterio;
    use App\CriterioItem;

    class CriterioController extends Controller{

        public function  obtener_criterios(){

            $criterios = Criterio::where('mantenimiento', 'S')->get();

            return response()->json($criterios);

        }

        public function detalle_criterio(Request $request){

            $criterio = Criterio::find($request->id_criterio);

            $detalle = app('db')->select("  SELECT 
                                                ID, 
                                                DESCRIPCION, 
                                                TO_CHAR(VALOR, '0.99') AS VALOR, 
                                                TO_CHAR(VALOR_NO_ISO, '0.99') AS VALOR_NO_ISO, 
                                                TO_CHAR(VALOR_P, '0.99') AS VALOR_P, 
                                                TO_CHAR(VALOR_NO_ISO_P, '0.99') AS VALOR_NO_ISO_P, 
                                                ID_CRITERIO,
                                                APLICA_ASESOR,
                                                APLICA_PRESTADOR
                                            FROM RRHH_IND_CRITERIO_ITEM
                                            WHERE ID_CRITERIO = $request->id_criterio
                                            AND DELETED_AT IS NULL
                                            ORDER BY ID DESC");

            /* 

                ASESOR
                - VALOR => Proceso ISO
                - VALOR_NO_ISO => Proceso NO ISO

                COLABORADOR
                - VALOR_P => Proceso ISO
                - VALOR_NO_ISO_P => Proceso NO ISO
            */

            $headers = [
                [
                    "text" => "Descripción",
                    "value" => "descripcion",
                    "sortable" => false,
                    "width" => "50%"
                ],
                [
                    "text" => "Asesor",
                    "value" => "asesor",
                    "sortable" => false,
                    "width" => "20%"
                ],
                [
                    "text" => "Colaborador",
                    "value" => "colaborador",
                    "sortable" => false,
                    "width" => "20%"
                ],
                [
                    "text" => "Acción",
                    "value" => "action",
                    "align" => "right",
                    "sortable" => false,
                    "width" => "10%"
                ]
            ];

            $data = [
                "items" => $detalle,
                "headers" => $headers,
                "criterio" => $criterio
            ];

            return response()->json($data);

        }

        public function registrar_item_criterio(Request $request){

            $criterio = Criterio::find($request->id_criterio);

            $item_criterio = new CriterioItem();
            $item_criterio->descripcion = $request->descripcion;
            $item_criterio->id_criterio = $request->id_criterio;
            $item_criterio->aplica_asesor = $request->aplica_asesor ? 'S' : null;
            $item_criterio->aplica_prestador = $request->aplica_prestador ? 'S' : null;
            $item_criterio->save();

            // Validar si se registra la ponderación
            if ($criterio->registrar_ponderacion) {
                
                $ponderaciones = [
                    [
                        "nombre" => "Excelente",
                        "valor" => 1
                    ],
                    [
                        "nombre" => "Bueno",
                        "valor" => 0.76
                    ],
                    [
                        "nombre" => "Regular",
                        "valor" => 0.53
                    ],
                    [
                        "nombre" => "Malo",
                        "valor" => 0
                    ]
                ];

                foreach ($ponderaciones as $ponderacion) {
                    
                    $result = app('db')->table('RRHH_IND_CRITERIO_PONDERACION')->insert([
                        "nombre" => $ponderacion["nombre"],
                        "id_criterio_item" => $item_criterio->id,
                        "valor" => $ponderacion["valor"]
                    ]);

                }

            }

            // Registrar en las áreas que corresponda
            foreach ($request->areas as $area) {
                
                $result = app('db')->table('RRHH_IND_CRITERIO_ITEM_AREA')->insert([
                    "id_item" => $item_criterio->id,
                    "codarea" => $area
                ]);

            }

            // Actualizar los valores

            if ($request->aplica_asesor) {
                
                // Si el item aplica para el asesor

                if ($criterio->valor) {
                    
                    // Si el criterio tiene valor ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->get();

                    $value = round($criterio->valor / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->update(['valor' => $value]);

                }

                if ($criterio->valor_no_iso) {
                    
                    // Si el criterio tiene valor NO ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->get();

                    $value = round($criterio->valor_no_iso / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->update(['valor_no_iso' => $value]);

                }

            }

            if ($request->aplica_prestador) {
                
                // Si el item aplica para el colaborador

                if ($criterio->valor) {
                    
                    // Si el criterio tiene valor ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->get();

                    $value = round($criterio->valor / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->update(['valor_p' => $value]);

                }

                if ($criterio->valor_no_iso) {
                    
                    // Si el criterio tiene valor NO ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->get();

                    $value = round($criterio->valor_no_iso / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->update(['valor_no_iso_p' => $value]);

                }

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "El item a sido registrado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function editar_item_criterio(Request $request){

            $criterio = Criterio::find($request->id_criterio);

            $item_criterio = CriterioItem::find($request->id);

            $item_criterio->descripcion = $request->descripcion;
            $item_criterio->aplica_asesor = $request->aplica_asesor ? 'S' : null;
            $item_criterio->aplica_prestador = $request->aplica_prestador ? 'S' : null;

            if (!$request->aplica_asesor) {
                
                $item_criterio->valor = null;
                $item_criterio->valor_no_iso = null;

            }

            if (!$request->aplica_prestador) {
                
                $item_criterio->valor_p = null;
                $item_criterio->valor_no_iso_p = null;

            }

            $item_criterio->save();

            // Eliminar los áreas para realizar la actualización
            $result = app('db')->table('RRHH_IND_CRITERIO_ITEM_AREA')->where('id_item', $item_criterio->id)->delete();

            // Registrar de nuevo las áreas
            foreach ($request->areas as $area) {
                
                $result = app('db')->table('RRHH_IND_CRITERIO_ITEM_AREA')->insert([
                    "id_item" => $item_criterio->id,
                    "codarea" => $area
                ]);

            }

             // Si el item aplica para el asesor

            if ($criterio->valor) {
                    
                // Si el criterio tiene valor ISO
                $items = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_asesor', 'S')
                            ->get();

                $value = round($criterio->valor / count($items), 2);

                $result = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_asesor', 'S')
                            ->update(['valor' => $value]);

            }

            if ($criterio->valor_no_iso) {
                
                // Si el criterio tiene valor NO ISO
                $items = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_asesor', 'S')
                            ->get();

                $value = round($criterio->valor_no_iso / count($items), 2);

                $result = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_asesor', 'S')
                            ->update(['valor_no_iso' => $value]);

            }

            // Si el item aplica para el colaborador

            if ($criterio->valor) {
                    
                // Si el criterio tiene valor ISO
                $items = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_prestador', 'S')
                            ->get();

                $value = round($criterio->valor / count($items), 2);

                $result = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_prestador', 'S')
                            ->update(['valor_p' => $value]);

            }

            if ($criterio->valor_no_iso) {
                
                // Si el criterio tiene valor NO ISO
                $items = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_prestador', 'S')
                            ->get();

                $value = round($criterio->valor_no_iso / count($items), 2);

                $result = CriterioItem::where('id_criterio', $criterio->id)
                            ->where('aplica_prestador', 'S')
                            ->update(['valor_no_iso_p' => $value]);

            }


            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "El item a sido actualizado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function detalle_item_criterio(Request $request){

            $item = CriterioItem::find($request->id);

            $id_areas = app('db')->select(" SELECT CODAREA
                                            FROM RRHH_IND_CRITERIO_ITEM_AREA
                                            WHERE ID_ITEM = $request->id");

            $areas = [];

            foreach ($id_areas as $id) {
                
                $areas [] = $id->codarea;

            }

            $item->areas = $areas;

            return response()->json($item);

        }

        public function eliminar_item_criterio(Request $request){

            $item_criterio = CriterioItem::find($request->id);

            $criterio = Criterio::find($item_criterio->id_criterio);
            
            $item_criterio->delete();
            
            if ($item_criterio->aplica_asesor) {
                
                // Si el item aplica para el asesor

                if ($criterio->valor) {
                    
                    // Si el criterio tiene valor ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->get();

                    $value = round($criterio->valor / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->update(['valor' => $value]);

                }

                if ($criterio->valor_no_iso) {
                    
                    // Si el criterio tiene valor NO ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->get();

                    $value = round($criterio->valor_no_iso / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_asesor', 'S')
                                ->update(['valor_no_iso' => $value]);

                }

            }

            if ($item_criterio->aplica_prestador) {
                
                // Si el item aplica para el colaborador

                if ($criterio->valor) {
                    
                    // Si el criterio tiene valor ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->get();

                    $value = round($criterio->valor / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->update(['valor_p' => $value]);

                }

                if ($criterio->valor_no_iso) {
                    
                    // Si el criterio tiene valor NO ISO
                    $items = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->get();

                    $value = round($criterio->valor_no_iso / count($items), 2);

                    $result = CriterioItem::where('id_criterio', $criterio->id)
                                ->where('aplica_prestador', 'S')
                                ->update(['valor_no_iso_p' => $value]);

                }

            }

            $data = [
                "status" => 200,
                "title" => "Excelente",
                "message" => "El item a sido eliminado exitosamente",
                "type" => "success"
            ];

            return response()->json($data);

        }

        public function asignar_areas(){

            $areas = app('db')->select("    SELECT *
                                            FROM RH_AREAS
                                            WHERE ESTATUS = 'A'");

            $items = app('db')->select("    SELECT *
                                            FROM RRHH_IND_CRITERIO_ITEM
                                            WHERE DELETED_AT IS NULL
                                            ORDER BY ID ASC");

            // Registrar para todos los items el área
            foreach ($items as $item) {
                
                foreach ($areas as $area) {
                    
                    $result = app('db')->table('RRHH_IND_CRITERIO_ITEM_AREA')->insert([
                        "id_item" => $item->id,
                        "codarea" => $area->codarea
                    ]);

                }

            }

            return response()->json($items);

        }

    }

?>