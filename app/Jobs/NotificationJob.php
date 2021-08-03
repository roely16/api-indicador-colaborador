<?php

namespace App\Jobs;

use App\Notificacion;

class NotificationJob extends Job
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

        $titulo = $this->data->titulo;
        $detalle = $this->data->detalle;
        $destinos = $this->data->destinos;

        foreach ($destinos as $destino) {
            
            $notificacion = new Notificacion();

            $notificacion->destino = $destino;
            $notificacion->titulo = $titulo;
            $notificacion->detalle = $detalle;
            $notificacion->save();

        }
        
    }
}
