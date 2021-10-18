<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Jobs\ExampleJob;
    use App\Jobs\EvaluacionJob;

    class JobController extends Controller{

        public function test_job(Request $request){

            $data = (object) [
                "nit" => "6450819-6"
            ];

            \Queue::push(new ExampleJob($data));

            return response()->json($request);

        }

        public function evaluacion_job(Request $request){

            if ($request->nit) {
                
                $data = (object) [
                    "date" => $request->date,
                    "nit" => $request->nit,
                    "codarea" => null
                ];

            }elseif ($request->codarea) {
                
                $data = (object) [
                    "date" => $request->date,
                    "codarea" => $request->codarea,
                    "nit" => null
                ];

            }

            \Queue::push(new EvaluacionJob($data));

            return response()->json($request);

        }

    }

?>