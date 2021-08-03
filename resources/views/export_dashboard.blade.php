@foreach($areas as $area)
    <h1>
        {{ $area->descripcion }}
    </h1>
    <table>
        <thead>
            <tr>
                <th>Colaborador</th>
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
    </table>
@endforeach