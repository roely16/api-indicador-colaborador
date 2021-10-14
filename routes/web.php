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

// Obtener los permisos del usuario
$router->post('/obtener_permisos_usuario', 'PermisoController@obtener_permisos_usuario');

// Registrar los permisos
$router->post('/registrar_permisos', 'PermisoController@registrar_permisos');

// Obtener todos los permisos habilitados
$router->post('/permisos_habilitados', 'PermisoController@permisos_habilitados');

// Eliminar permisos
$router->post('/eliminar_permisos', 'PermisoController@eliminar_permisos');

// Verificar permisos
$router->post('/verificar_permisos', 'PermisoController@verificar_permisos');

// Registrar la evaluación
$router->post('/registrar_evaluacion', 'EvaluacionController@registrar_evaluacion');

// Obtener evaluaciones
$router->post('/obtener_evaluaciones', 'EvaluacionController@obtener_evaluaciones');

// Dashboard área
$router->post('/dashboard_area', 'DashboardController@dashboard_area');

// Obtener todas las areas con colaboradores
$router->post('/obtener_areas_colaboradores', 'AreaController@obtener_areas_colaboradores');

// Registrar grupo
$router->post('/registrar_grupo', 'SOController@registrar_grupo');

// Obtener los grupos 
$router->post('/obtener_grupos', 'SOController@obtener_grupos');

// Agregar integrante a grupo
$router->post('/agregar_integrante', 'SOController@agregar_integrante');

// Agregar una sección completa
$router->post('/agregar_seccion', 'SOController@agregar_seccion');

// Obtener el detalle de un grupo
$router->post('/integrantes_grupo', 'SOController@integrantes_grupo');

// Obtener las actividades del grupo
$router->post('/actividades_grupo', 'SOController@actividades_grupo');

// Registrar actividad
$router->post('/registrar_actividad', 'SOController@registrar_actividad');

// Asignar actividad
$router->post('/asignar_actividad', 'SOController@asignar_actividad');

// Calificar responsable
$router->post('/calificar_responsable', 'SOController@calificar_responsable');

// Obtener las evaluaciones de desempeño
$router->post('/obtener_evaluaciones_performance', 'PerformanceController@obtener_evaluaciones');

// Obtener las temporadas
$router->post('/obtener_temporadas', 'PerformanceController@obtener_temporadas');

// Detalle del grupo
$router->post('/detalle_grupo', 'SOController@detalle_grupo');

// Editar el grupo
$router->post('/editar_grupo', 'SOController@editar_grupo');

// Eliminar grupo
$router->post('/eliminar_grupo', 'SOController@eliminar_grupo');

// Eliminar integrantes
$router->post('/eliminar_integrantes', 'SOController@eliminar_integrantes');

// Eliminar actividad
$router->post('/eliminar_actividad', 'SOController@eliminar_actividad');

// Detalles de la actividad
$router->post('/detalle_actividad', 'SOController@detalle_actividad');

// Editar actividad
$router->post('/editar_actividad', 'SOController@editar_actividad');

// Eliminar evaluación
$router->post('/eliminar_evaluacion', 'EvaluacionController@eliminar_evaluacion');

// Detalle del reporte
$router->post('/detalle_reporte', 'ReporteController@detalle_reporte');

// Editar evaluación
$router->post('/editar_evaluacion', 'EvaluacionController@editar_evaluacion');

// Eliminar responsable de actividad
$router->post('/eliminar_responsable_actividad', 'SOController@eliminar_responsable_actividad');

// Obtener el perfil de un colaborador
$router->post('/obtener_perfil', 'CompetenciaController@obtener_perfil');

// Registrar evaluación de competencias
$router->post('/registrar_evaluacion_competencia', 'CompetenciaController@registrar_evaluacion');

// Detalle de la evaluación de competencias
$router->post('/detalle_evaluacion_competencia', 'CompetenciaController@detalle_evaluacion');

// Editar evaluación de competencias
$router->post('/editar_evaluacion_competencia', 'CompetenciaController@editar_evaluacion');

// Eliminar evaluación de competencias
$router->post('/eliminar_evaluacion_competencia', 'CompetenciaController@eliminar_evaluacion');

// Obtener las evaluaciones
$router->post('/obtener_evaluaciones_competencia', 'CompetenciaController@obtener_evaluaciones');

// Obtener los perfiles
$router->post('/obtener_perfiles', 'PerfilController@obtener_perfiles');

// Registrar un perfil
$router->post('/registrar_perfil', 'PerfilController@registrar');

// Información del colaborador
$router->post('/info_colaborador', 'PerfilController@info_colaborador');

// Eliminar perfil
$router->post('/eliminar_perfil', 'PerfilController@eliminar');

// Detalle del perfil
$router->post('/detalle_perfil', 'PerfilController@detalle');

// Editar el perfil
$router->post('/editar_perfil', 'PerfilController@editar');

// Indicador individual
$router->post('/indicador_individual', 'DashboardController@indicador_individual');

// Registrar item de criterio
$router->post('/registrar_item_criterio', 'CriterioController@registrar_item_criterio');

// Eliminar item de criterio
$router->post('/eliminar_item_criterio', 'CriterioController@eliminar_item_criterio');

// Detalle item de criterio
$router->post('/detalle_item_criterio', 'CriterioController@detalle_item_criterio');

// Asignar áreas
$router->post('/asignar_areas', 'CriterioController@asignar_areas');

// Editar item de criterio
$router->post('/editar_item_criterio', 'CriterioController@editar_item_criterio');

// Obtener periodos para evaluación de competencias
$router->post('/obtener_periodos', 'CompetenciaController@obtener_periodos');

// Registrar un nuevo periodo de evaluación de competencias
$router->post('/registrar_periodo', 'CompetenciaController@registrar_periodo');

// Eliminar un periodo 
$router->post('/eliminar_periodo', 'CompetenciaController@eliminar_periodo');

// Editar un periodo
$router->post('/editar_periodo', 'CompetenciaController@editar_periodo');

// Posponer evaluación de competencias
$router->post('/posponer_evaluacion', 'CompetenciaController@posponer_evaluacion');

// Obtener todas las areas
$router->post('/obtener_todas_areas', 'AreaController@obtener_todas_areas');

// Equipo Indicadores
$router->post('/equipo_indicadores', 'DashboardController@equipo_indicadores');

// Generar los datos para exportar a Excel 
$router->post('/datos_excel', 'ExportController@datos_excel');

// Obtener el seguimiento de la evaluación
$router->post('/obtener_seguimiento', 'CompetenciaController@obtener_seguimiento');

// Registrar actividad de seguimiento
$router->post('/registrar_actividad_seguimiento', 'CompetenciaController@registrar_actividad');

// Adjuntar archivos a la actividad
$router->post('/subir_archivos_actividad', 'CompetenciaController@subir_archivos_actividad');

// Detalle de la actividad de seguimiento
$router->post('/detalle_actividad_seguimiento', 'CompetenciaController@detalle_actividad');

// Editar actividad de seguimiento
$router->post('/editar_actividad_seguimiento', 'CompetenciaController@editar_actividad');

// Eliminar archivos
$router->post('/eliminar_archivos_actividad', 'CompetenciaController@eliminar_archivos');

// Eliminar actividad de seguimiento
$router->post('/eliminar_actividad_seguimiento', 'CompetenciaController@eliminar_actividad');

// Eliminar al colaborador de un perfil
$router->post('/eliminar_colaborador_perfil', 'PerfilController@eliminar_colaborador_perfil');

// Agregar un colaborador a un perfil
$router->post('/agregar_colaborador_perfil', 'PerfilController@agregar_colaborador_perfil');

// Obtener el detalle del perfil de un colaborador para evaluación
$router->post('/detalle_perfil_colaborador', 'CompetenciaController@detalle_perfil_colaborador');

// Test mail
$router->post('/test_mail', 'MailController@test');

// Corregir evaluaciones de competencias
$router->post('/corregir_evaluaciones_competencias', 'CompetenciaController@corregir_evaluaciones');

// Corregir el seguimiento de las evaluaciones de competencias 
$router->post('/corregir_seguimiento', 'CompetenciaController@corregir_seguimiento');

// Verificar el cumplimiento de una actividad de seguimiento
$router->post('/cumplimiento_actividad', 'CompetenciaController@cumplimiento_actividad');

// Obtener las notificaciones
$router->post('/obtener_notificaciones', 'NotificacionController@obtener_notificaciones');

// Eliminar la notificación
$router->post('/eliminar_notificacion', 'NotificacionController@eliminar_notificacion');

// Filtro de un periodo de evaluaciones de competencias
$router->post('/filtro_periodo', 'CompetenciaController@filtro_periodo');

// Test Jobs
$router->post('/test_job', 'JobController@test_job');

// Probar Job para evaluaciones
$router->post('/evaluacion_job', 'JobController@evaluacion_job');

// Export Dashboard
$router->post('/export_dashboard', 'DashboardController@export_dashboard');

$router->post('/export_dashboard_view', 'DashboardController@export_dashboard_view');

// SGS
// Obtener actividades
$router->post('/sgs_obtener_actividades', 'SGSActividadController@obtener_actividades');

// Registrar actividad
$router->post('/sgs_registrar_actividad', 'SGSActividadController@registrar_actividad');

// Editar actividad
$router->post('/sgs_editar_actividad', 'SGSActividadController@editar_actividad');

// Eliminar actividad
$router->post('/sgs_eliminar_actividad', 'SGSActividadController@eliminar_actividad');

// Detalle de la actividad
$router->post('/sgs_detalle_actividad', 'SGSActividadController@detalle_actividad');

// Obtener las evaluaciones
$router->post('/sgs_obtener_evaluaciones', 'EvaluacionSGSController@obtener_evaluaciones');

// Registrar evaluación
$router->post('/sgs_registrar_evaluacion', 'EvaluacionSGSController@registrar_evaluacion');

// Eliminar evaluación
$router->post('/sgs_eliminar_evaluacion', 'EvaluacionSGSController@eliminar_evaluacion');

// Detalles de la evaluación
$router->post('/sgs_detalle_evaluacion', 'EvaluacionSGSController@detalle_evaluacion');

// Editar evaluación
$router->post('/sgs_editar_evaluacion', 'EvaluacionSGSController@editar_evaluacion');

// Obtener las actividades disponibles para asignar
$router->post('/sgs_actividades_disponibles', 'EvaDetalleSGSController@actividades_disponibles');

// Registrar actividades a una evaluación
$router->post('/sgs_agregar_actividad_eva', 'EvaDetalleSGSController@agregar_actividades');

// Obtener las actividades asignadas a una evaluación
$router->post('/sgs_actividades_evaluacion', 'EvaDetalleSGSController@actividades_evaluacion');

// Agregar responsables a una actividad de SGS
$router->post('/sgs_asignar_responsables', 'EvaDetalleSGSController@asignar_responsables');

// Obtener los responsables de una actividad
$router->post('/sgs_responsables_actividad', 'EvaDetalleSGSController@obtener_responsables_actividad');

// Obtener colaboradores disponibles
$router->post('/sgs_colaboradores_disponibles', 'EvaDetalleSGSController@colaboradores_disponibles');

// Actualizar el cumplimiento de una actividad
$router->post('/sgs_actualizar_cumplimiento', 'EvaDetalleSGSController@actualizar_cumplimiento');

// Actualizar el porcentaje de una actividad 
$router->post('/sgs_actualizar_porcentaje', 'EvaDetalleSGSController@actualizar_porcentaje');

// Eliminar los responsables de una actividad
$router->post('/sgs_eliminar_responsables', 'EvaDetalleSGSController@eliminar_responsables');