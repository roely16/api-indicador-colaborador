<?php 

    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class ActividadSGS extends Model{
        
        protected $table = "RRHH_IND_SGS_ACTIVIDAD";

        use SoftDeletes;
        
        protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    }

?>