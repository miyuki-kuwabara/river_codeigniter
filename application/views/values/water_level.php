<h1><?php eh($measure_source_name); ?>(水位)</h1>
<hr/>
<dl class="compact">
    <dt>単位</dt>
    <dd><?php eh($water_level_unit);?></dd>
    <dt>最新</dt>
    <dd><?php measured_value($latest['water_level_value'], $latest['water_level_flags']); ?> <?php eh($water_level_unit); ?> (<?php eh($latest['measured_at'])?>)</dd>
</dl>
<hr/>
<table>
    <tbody>
<?php $prev_date = null; ?>
<?php foreach ($measured_data as $row):
    $difference = 0;
    if (is_measured_value_enable($row['water_level_value'], $row['water_level_flags'])) {
        if (is_measured_value_enable($measured_data_last['water_level_value'], $measured_data_last['water_level_flags'])) {
            $difference = $row['water_level_value'] - $measured_data_last['water_level_value'];
        }
        $measured_data_last = $row;
    } ?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
            <td><?php if (0 < $difference): ?><span class="increase"><?php endif; measured_value($row['water_level_value'], $row['water_level_flags']); if (0 < $difference): ?></span><?php endif; ?></td>
            <td><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?><span class="increse">↑</span><?php else: ?>→<?php endif; ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>