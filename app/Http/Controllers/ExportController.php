<?php 

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    use App\Menu;
    use App\Permiso;

    class ExportController extends Controller{

        public function datos_excel(Request $request){

            $areas = implode(",", $request->codarea);

            $evaluaciones = app('db')
                            ->select("  SELECT 
                                            T2.NIT, 
                                            CONCAT(T2.NOMBRE, CONCAT(' ', T2.APELLIDO)) AS NOMBRE,
                                            T1.MES,
                                            T1.CALIFICACION,
                                            T3.DESCRIPCION AS SECCION
                                        FROM RRHH_IND_EVALUACION T1
                                        INNER JOIN RH_EMPLEADOS T2
                                        ON T1.ID_PERSONA = T2.NIT
                                        INNER JOIN RH_AREAS T3
                                        ON T2.CODAREA = T3.CODAREA
                                        WHERE T1.ID_CRITERIO = $request->criterio
                                        AND T1.MES = '$request->date'
                                        AND T1.ID_PERSONA IN (
                                            SELECT NIT
                                            FROM RH_EMPLEADOS
                                            WHERE CODAREA IN ($areas)
                                            AND STATUS = 'A'
                                        )

                            ");

            return response()->json($evaluaciones);

        }

    }