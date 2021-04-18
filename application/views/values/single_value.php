<?php 
$value_key = "{$value_type}_value";
$flags_key = "{$value_type}_flags";
$unit_varname = "{$value_type}_unit";
?>
<h1><?php eh($measure_source_name); ?>(<?php eh($value_type_name); ?>)</h1>
<hr/>
<dl class="compact">
    <dt>単位</dt>
    <dd><?php eh($$unit_varname);?></dd>
    <dt>最新</dt>
    <dd><?php measured_value($latest[$value_key], $latest[$flags_key]); ?> <?php eh($$unit_varname); ?> (<?php eh($latest['measured_at'])?>)</dd>
</dl>
<hr/>
<table>
    <tbody>
<?php $prev_date = null; ?>
<?php foreach ($measured_data as $row):
    $difference = 0;
    if (is_measured_value_enable($row[$value_key], $row[$flags_key])) {
        if (is_measured_value_enable($measured_data_last[$value_key], $measured_data_last[$flags_key])) {
            $difference = $row[$value_key] - $measured_data_last[$value_key];
        }
        $measured_data_last = $row;
    } ?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
            <td><?php if (0 < $difference): ?><span class="increase"><?php endif; measured_value($row[$value_key], $row[$flags_key]); if (0 < $difference): ?></span><?php endif; ?></td>
            <td><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?><span class="increse">↑</span><?php else: ?>→<?php endif; ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>