<?php 

    namespace App\Exports;

    use App\EvaluacionCompetencia;

    use Illuminate\Contracts\View\View;
    use Maatwebsite\Excel\Concerns\FromView;
    use Maatwebsite\Excel\Concerns\ShouldAutoSize;
    use Maatwebsite\Excel\Concerns\WithStyles;
    use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

    use Illuminate\Http\Request;

    class DashboardExport implements FromView, ShouldAutoSize, WithStyles{

        protected $data;

        public function __construct($data){

            $this->data = $data;

        }

        public function styles(Worksheet $sheet){
            
            return [
                // Style the first row as bold text.
                1    => ['font' => ['bold' => true]],
            ];
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