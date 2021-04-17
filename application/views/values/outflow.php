<h1><?php eh($measure_source_name); ?>(放流量)</h1>
<hr/>
<dl class="compact">
    <dt>単位</dt>
    <dd><?php eh($outflow_unit);?></dd>
    <dt>最新</dt>
    <dd><?php measured_value($latest['outflow_value'], $latest['outflow_flags']); ?> <?php eh($outflow_unit); ?> (<?php eh($latest['measured_at'])?>)</dd>
</dl>
<hr/>
<table>
    <tbody>
<?php $prev_date = null; ?>
<?php foreach ($measured_data as $row):
    $difference = 0;
    if (is_measured_value_enable($row['outflow_value'], $row['outflow_flags'])) {
        if (is_measured_value_enable($measured_data_last['outflow_value'], $measured_data_last['outflow_flags'])) {
            $difference = $row['outflow_value'] - $measured_data_last['outflow_value'];
        }
        $measured_data_last = $row;
    } ?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
            <td><?php if (0 < $difference): ?><span class="increase"><?php endif; measured_value($row['outflow_value'], $row['outflow_flags']); if (0 < $difference): ?></span><?php endif; ?></td>
            <td><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?><span class="increse">↑</span><?php else: ?>→<?php endif; ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>