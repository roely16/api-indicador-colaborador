<?php 

    namespace App;

    use Illuminate\Database\Eloquent\Model;

    class Integrante extends Model{
        
        protected $primaryKey = null;

        public $incrementing = false;
        
        protected $table = "RRHH_IND_INT_GRUPO";

    }

?>