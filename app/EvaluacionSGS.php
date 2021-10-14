<?php 

    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class EvaluacionSGS extends Model{
        
        protected $table = "RRHH_IND_SGS_EVALUACION";

        use SoftDeletes;

    }

?>