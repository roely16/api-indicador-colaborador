<?php

namespace App\Jobs;

class ExampleJob extends Job
{

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data){

        $this->data = $data;

    }

    /**
     * Execute the job.
     *
     * @return void
     */

    public function handle(){

        $nit = $this->data->nit;

        $result = app('db')->select("   SELECT *
                                        FROM RH_EMPLEADOS
                                        WHERE NIT = '$nit'");

        
    }
}
