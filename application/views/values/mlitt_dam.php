<h1><?php eh($measure_source_name); ?></h1>
<hr/>
<table>
    <thead>
        <tr>
            <td colspan="2"></td>
            <th colspan="2">流入 <?php if (isset($inflow_unit)): ?>(<?php eh($inflow_unit); ?>)<? endif; ?></th>
            <th colspan="2">放流 <?php if (isset($outflow_unit)): ?>(<?php eh($outflow_unit); ?>)<? endif; ?></th>
            <th colspan="2">貯水率 <?php if (isset($percentage_unit)): ?>(<?php eh($percentage_unit); ?>)<? endif; ?></th>
            <th colspan="2">貯水量 <?php if (isset($amount_unit)): ?>(<?php eh($amount_unit); ?>)<? endif; ?></th>
        </tr>
    </thead>
    <tbody>
<?php $prev_date = null; ?>
<?php foreach ($measured_data as $row):
    $differences = array_reduce(
        array('inflow', 'outflow', 'percentage', 'amount'),
        function ($array, $field) use (&$measured_data_last, $row) {
            $value_key = "{$field}_value";
            $flags_key = "{$field}_flags";
            $difference = 0;
            if (is_measured_value_enable($row[$value_key], $row[$flags_key])) {
                if (is_measured_value_enable($measured_data_last[$value_key], $measured_data_last[$flags_key])) {
                    $difference = $row[$value_key] - $measured_data_last[$value_key];
                }
                $measured_data_last[$value_key] = $row[$value_key];
                $measured_data_last[$flags_key] = $row[$flags_key];
            }
            $array[$field] = $difference;
            return $array;
        }, array()); ?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
<?php   foreach ($differences as $field => $difference): ?>
            <td><?php if (0 < $difference): ?><span class="increase"><?php endif; measured_value($row["{$field}_value"], $row["{$field}_flags"]); if (0 < $difference): ?></span><?php endif; ?></td>
            <td><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?><span class="increse">↑</span><?php else: ?>→<?php endif; ?></td>
<?php   endforeach; ?>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
