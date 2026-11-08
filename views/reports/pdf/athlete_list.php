

<?php if (isset($filters) && !empty(array_filter($filters))): ?>
<div class="filters-info">
    <?php
    $appliedFilters = [];
    if (!empty($filters['category'])) {
        $appliedFilters[] = "Categoría: " . htmlspecialchars($filters['category']);
    }
    if (!empty($filters['age'])) {
        $appliedFilters[] = "Edad: " . htmlspecialchars($filters['age']);
    }
    if (!empty($filters['status'])) {
        $statusTranslations = [
            'active' => 'Activo',
            'inactive' => 'Inactivo',
            'suspended' => 'Suspendido'
        ];
        $statusText = $statusTranslations[$filters['status']] ?? ucfirst($filters['status']);
        $appliedFilters[] = "Estatus: " . $statusText;
    }
    if (!empty($filters['athlete_month'])) {
        $months = [
            '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril',
            '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto',
            '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
        ];
        $monthText = $months[$filters['athlete_month']] ?? $filters['athlete_month'];
        $appliedFilters[] = "Mes: " . $monthText;
    }
    if (!empty($filters['athlete_year'])) {
        $appliedFilters[] = "Año: " . htmlspecialchars($filters['athlete_year']);
    }
    if (!empty($filters['athlete_start_date'])) {
        $appliedFilters[] = "Desde: " . date('d/m/Y', strtotime($filters['athlete_start_date']));
    }
    if (!empty($filters['athlete_end_date'])) {
        $appliedFilters[] = "Hasta: " . date('d/m/Y', strtotime($filters['athlete_end_date']));
    }
    
    // Agregar el total de atletas al final
    $totalAtletasGlobal = isset($athletes) ? count($athletes) : 0;
    
    // Calcular totales por estado
    $totalActivos = 0;
    $totalInactivos = 0;
    $totalSuspendidos = 0;
    
    if (isset($athletes) && !empty($athletes)) {
        foreach ($athletes as $Athlete) {
            switch ($Athlete['status']) {
                case 'Activo':
                    $totalActivos++;
                    break;
                case 'Inactivo':
                    $totalInactivos++;
                    break;
                case 'Suspendido':
                    $totalSuspendidos++;
                    break;
            }
        }
    }
    
    if (!empty($appliedFilters)) {
        echo implode(' | ', $appliedFilters);
        echo " | <strong>Total: " . $totalAtletasGlobal . " atleta" . ($totalAtletasGlobal != 1 ? "s" : "") . "</strong>";
    } else {
        echo "Todos los atletas | <strong>Total: " . $totalAtletasGlobal . " atleta" . ($totalAtletasGlobal != 1 ? "s" : "") . "</strong>";
    }
    
    // Mostrar totales por estado
    echo "<br><strong>Activos:</strong> " . $totalActivos;
    echo " | <strong>Inactivos:</strong> " . $totalInactivos;
    echo " | <strong>Suspendidos:</strong> " . $totalSuspendidos;
    ?>
</div>
<?php endif; ?>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Edad</th>
            <th>Categoría</th>
            <th>Representante</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Estatus</th>
            <th>Fecha de Registro</th>
        </tr>
    </thead>
    <tbody>
        <?php if (isset($athletes) && !empty($athletes)): ?>
            <?php
            $categoryRanges = [
                'Infantil' => 'Infantil (8-10 años)',
                'Pre-juvenil' => 'Pre-juvenil (11-12 años)',
                'Juvenil' => 'Juvenil (13-15 años)',
                'Junior' => 'Junior (16-18 años)'
            ];
            $counter = 1;
            foreach ($athletes as $Athlete):
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($Athlete['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($Athlete['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($Athlete['age']); ?></td>
                    <td><?php
                        $category = $Athlete['category'];
                        echo htmlspecialchars($categoryRanges[$category] ?? $category);
                    ?></td>
                    <td><?php echo htmlspecialchars($Athlete['parent_name']); ?></td>
                    <td><?php echo htmlspecialchars($Athlete['parent_phone']); ?></td>
                    <td><?php echo htmlspecialchars($Athlete['parent_email']); ?></td>
                    <td><?php echo htmlspecialchars($Athlete['status']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($Athlete['registration_date'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="pdf-no-results">No hay atletas para mostrar según los filtros aplicados.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>