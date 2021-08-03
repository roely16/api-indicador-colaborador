<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Notificacion;

    class NotificacionController extends Controller{

        public function obtener_notificaciones(Request $request){

            $notificaciones = Notificacion::where('destino', $request->nit)->get();

            foreach ($notificaciones as &$notificacion) {
                
                $notificacion->title = $notificacion->titulo;
                $notificacion->subtitle = $notificacion->detalle;

            }

            return response()->json($notificaciones);

        }

        public function eliminar_notificacion(Request $request){

            $notificacion = Notificacion::find($request->id);
            $notificacion->delete();

            return response()->json($notificacion);

        }

    }

?>