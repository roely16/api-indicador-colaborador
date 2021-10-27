    <table>
    @foreach($areas as $area)
        <thead>
            <tr>
                <th>Colaborador</th>
                <th>Área</th>
                <th>Mes</th>
                <th>Productividad</th>
                <th>ISO 9001</th>
                <th>Metodología 5'S</th>
                <th>Oficina Verde</th>
                <th>Convivencia</th>
                <th>SSO</th>
                <th>Desempeño</th>
                <th>Competencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($area->empleados as $empleado)
                <tr>
                    <td>
                        {{ $empleado->nombre }} {{ $empleado->apellido }}
                    </td>
                    <td>
                        {{ $area->descripcion }}
                    </td>
                    <td>
                        {{ $empleado->mes }}
                    </td>
                    @foreach($empleado->criterios as $criterio)

                        <?php if(property_exists($criterio, "calificacion")) : ?>
                            <td>
                                {{ round($criterio->calificacion, 2) }}
                            </td>
                        <?php else : ?>
                            <td>
                                P
                            </td>
                        <?php endif; ?>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        @endforeach
    </table>

    <style>

        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
        }

    </style>
