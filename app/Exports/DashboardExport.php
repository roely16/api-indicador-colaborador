<?php 

    namespace App\Exports;

    use App\EvaluacionCompetencia;

    use Illuminate\Contracts\View\View;
    use Maatwebsite\Excel\Concerns\FromView;

    use Illuminate\Http\Request;

    class DashboardExport implements FromView{

        protected $data;

        public function __construct($data){

            $this->data = $data;

        }

        public function view(): View{

            $result = app('App\Http\Controllers\DashboardController')->dashboard_area($this->data);

            $areas = $result->getData();

            return view('export_dashboard', [
                'areas' => $areas
            ]);
            
        }
    }

?>