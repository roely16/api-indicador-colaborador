<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\EvaluacionSGS;

    class EvaluacionSGSController extends Controller{
        
        public function obtener_evaluaciones(Request $request){

            $evaluaciones = app('db')->select(" SELECT *
                                                FROM RRHH_IND_SGS_EVALUACION
                                                WHERE DELETED_AT IS NULL
                                                ORDER BY ID DESC");

            return response()->json($evaluaciones);

        }

        public function registrar_evaluacion(Request $request){

            $evaluacion = new EvaluacionSGS();
            $evaluacion->nombre = $request->nombre;
            $evaluacion->descripcion = $request->descripcion;
            $evaluacion->mes = $request->mes;
            $evaluacion->save();

            $response = [
                "status" => 200,
                "evaluacion" => $evaluacion
            ];

            return response()->json($response);

        }

        public function eliminar_evaluacion(Request $request){

            $evaluacion = EvaluacionSGS::find($request->id);
            $evaluacion->delete();
            
            $response = [
                "status" => 200,
                "actividad" => $evaluacion
            ];

            return response()->json($response);

        }

        public function detalle_evaluacion(Request $request){

            $evaluacion = EvaluacionSGS::find($request->id);

            $response = [
                "status" => 200,
                "evaluacion" => $evaluacion
            ];

            return response()->json($response);

        }

        public function editar_evaluacion(Request $request){
            
            $evaluacion = EvaluacionSGS::find($request->id);
            $evaluacion->nombre = $request->nombre;
            $evaluacion->descripcion = $request->descripcion;
            $evaluacion->mes = $request->mes;
            $evaluacion->save();

            $response = [
                "status" => 200,
                "evaluacion" => $evaluacion
            ];

            return response()->json($response);

        }

    }

?>