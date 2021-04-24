<h1><?php eh($measure_source_name); ?></h1>
<hr/>
<table class="measured-data multi-column-list">
    <thead>
        <tr>
            <td colspan="2"></td>
            <th colspan="2">流入<?php if (isset($inflow_unit)): ?><br /><?php eh($inflow_unit); ?><? endif; ?></th>
            <th colspan="2">放流<?php if (isset($outflow_unit)): ?><br /><?php eh($outflow_unit); ?><? endif; ?></th>
            <th colspan="2">貯水率<?php if (isset($percentage_unit)): ?><br /><?php eh($percentage_unit); ?><? endif; ?></th>
            <th colspan="2">貯水量<?php if (isset($amount_unit)): ?><br /><?php eh($amount_unit); else: ?><br />千m3<? endif; ?></th>
        </tr>
    </thead>
    <tbody>
<?php
// 先に増減を計算しておく
$value_types = array(
    'inflow' => 2,
    'outflow' => 2,
    'percentage' => 1,
    'amount' => 0);
$output = array();
$last = end($measured_data);
reset($measured_data);
foreach (array_reverse($measured_data) as $row) {
    $data = array_reduce(
        array_keys($value_types),
        function ($array, $field) use (&$last, $row) {
            $value_key = "{$field}_value";
            $flags_key = "{$field}_flags";
            $diff_key = "{$field}_diff";
            $array[$diff_key] = 0;
            if (is_measured_value_enable($row[$value_key], $row[$flags_key])) {
                if (is_measured_value_enable($last[$value_key], $last[$flags_key])) {
                    $difference = $row[$value_key] - $last[$value_key];
                }
                $last[$value_key] = $row[$value_key];
                $last[$flags_key] = $row[$flags_key];
            }
            $array[$diff_key] = $difference;
            return $array;
        }, $row);
    array_unshift($output, $data);
}
array_pop($output);

$prev_date = null; 
foreach ($output as $row):?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
<?php   foreach ($value_types as $value_type => $decimal): ?>
            <td<?php if (0 < $row["{$value_type}_diff"]): ?> class="increase"<?php endif; ?>><?php measured_value($row["{$value_type}_value"], $row["{$value_type}_flags"], $decimal); ?></td>
            <td<?php if (0 < $row["{$value_type}_diff"]): ?> class="increase"<?php endif; ?>><?php if ($row["{$value_type}_diff"] < 0): ?>↓<?php elseif (0 < $row["{$value_type}_diff"]): ?>↑<?php else: ?>→<?php endif; ?></td>
<?php   endforeach; ?>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>
