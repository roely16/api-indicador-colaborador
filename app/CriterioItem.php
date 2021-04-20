<?php 

    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CriterioItem extends Model{
        
        use SoftDeletes;
        
        protected $table = "RRHH_IND_CRITERIO_ITEM";

    }

?>