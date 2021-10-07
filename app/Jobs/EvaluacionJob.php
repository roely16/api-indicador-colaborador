<?php

namespace App\Jobs;

use App\Http\Controllers\ReporteController;
use Illuminate\Http\Request;

use App\Evaluacion;

class EvaluacionJob extends Job{

    protected $data;

    protected $areas = [
        [
            "nombre" => "cinco_s",
            "id" => 3
        ],
        [
            "nombre" => "oficina_verde",
            "id" => 4
        ],
        [
            "nombre" => "iso",
            "id" => 2
        ]
    ];

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct(){

    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle(){

        $month = date('Y-m');

        /*
            Por cada área validar cada uno de los integrantes del equipo
        */
        
        foreach ($this->areas as $area) {
            
            $empleados = app('db')->select("    SELECT 
                                                    NOMBRE, 
                                                    APELLIDO, 
                                                    NIT
                                                FROM RH_EMPLEADOS
                                                WHERE CODAREA = 8
                                                AND STATUS = 'A'
                                                AND NIT = '6450819-6'");

            foreach ($empleados as $empleado) {
                
                /*
                    Validar si ya existe una evaluación
                */

                $evaluacion = Evaluacion::
                            where('id_criterio', $area["id"])
                            ->where('id_persona', $empleado->nit)
                            ->where('mes', $month)
                            ->first();

                if ($evaluacion) {
                    
                    $empleado->edit = true;

                    $request = new Request();

                    $request->replace([
                        "nit" => $empleado->nit,
                        "id_evaluacion" => $evaluacion->id
                    ]);

                    $result = app('App\Http\Controllers\ReporteController')->detalle_reporte($request);

                    $data = $result->getData();

                    foreach ($data->items as &$item) {
                        
                        $automatico = true;

                        if (!$item->funcion_calculo  || $item->editable) {
                            
                            $automatico = false;

                            break;

                        }

                    }

                    $empleado->automatic = $automatico;

                    if ($empleado->automatic) {
                        
                        $total = 0;

                        $evaluacion_items = [];

                        foreach ($data->items as &$item) {

                            $total += $item->calificacion;

                        }

                        $empleado->calificacion = round($total / count($data->items), 2);

                        $request_evaluacion = new Request();

                        $request_evaluacion->replace([
                            "id_evaluacion" => $evaluacion->id,
                            "criterio" => $data->criterio,
                            "id_persona" => $empleado->nit,
                            "month" => $month,
                            "calificacion" => $empleado->calificacion,
                            "items" => $data->items
                        ]);

                        /* Editar la evaluación */

                        $result_evaluacion = app('App\Http\Controllers\EvaluacionController')->editar_evaluacion($request_evaluacion);

                        $empleado->evaluacion = $request_evaluacion;
                        $empleado->result_evaluacion = $result_evaluacion;

                    }

                    $empleado->data = $data;

                }else{

                    $empleado->edit = false;

                    $request = new Request();

                    $request->replace([
                        "nit" => $empleado->nit,
                        "url" => $area["nombre"],
                        "month" => $month
                    ]);
            
                    $result = app('App\Http\Controllers\ReporteController')->datos_reporte($request);

                    $data = $result->getData();

                    /* 
                        Por cada empleado validar si los items tiene función de calculo automatico y si no son editables
                    */
                    
                    foreach ($data->items as &$item) {
                        
                        $automatico = true;

                        if (!$item->funcion_calculo  || $item->editable) {
                            
                            $automatico = false;

                            break;

                        }

                    }

                    $empleado->automatic = $automatico;

                    /*
                        Si el empleado tiene cálculo automatico obtener la calificación
                    */

                    if ($empleado->automatic) {
                        
                        $total = 0;

                        $evaluacion_items = [];

                        foreach ($data->items as &$item) {

                            $total += $item->calificacion;

                        }

                        $empleado->calificacion = round($total / count($data->items), 2);

                        $request_evaluacion = new Request();

                        $request_evaluacion->replace([
                            "criterio" => $data->criterio,
                            "id_persona" => $empleado->nit,
                            "month" => $month,
                            "calificacion" => $empleado->calificacion,
                            "items" => $data->items
                        ]);

                        /* Registrar la evaluación */

                        $result_evaluacion = app('App\Http\Controllers\EvaluacionController')->registrar_evaluacion($request_evaluacion);

                        $empleado->evaluacion = $request_evaluacion;
                        $empleado->result_evaluacion = $result_evaluacion;

                    }

                }
                

            }

        }

        dd($empleados);

    }

}
