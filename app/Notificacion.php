<?php 

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    use Illuminate\Database\Eloquent\SoftDeletes;

    class Notificacion extends Model{
        
        use SoftDeletes;
        
        protected $table = "RRHH_IND_NOTIFICACION";

    }

?>