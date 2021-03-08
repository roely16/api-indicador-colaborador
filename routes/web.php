<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->post('/login', 'LoginController@login');

// Obtener el menu principal
$router->post('/obtener_menu', 'HomeController@obtener_menu');

// Obtener las areas
$router->post('/obtener_areas', 'AreaController@obtener_areas');

// Obtener los colaboradores de un area
$router->post('/obtener_colaboradores', 'AreaController@obtener_colaboradores');

// Obtener los reportes
$router->post('/obtener_reportes', 'ReporteController@obtener_reportes');

// Obtener los datos del reporte
$router->post('/datos_reporte', 'ReporteController@datos_reporte');

// Obtener criterios para sección de mantenimiento
$router->post('/obtener_criterios', 'CriterioController@obtener_criterios');

// Obtener el detalle del criterio seleccionado
$router->post('/detalle_criterio', 'CriterioController@detalle_criterio');