<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Mail\MailtrapExample;
    use Illuminate\Support\Facades\Mail;

    class MailController extends Controller{
    
        public function test(){

            try {
                
                Mail::to('hchur@muniguate.com')->send(new MailtrapExample());

            } catch (Swift_TransportException $e) {
                
                return $ge->getMessage();

            }

            return 'A message has been sent!';

        }

    }

?>