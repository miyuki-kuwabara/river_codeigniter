<h1><?php eh($measure_source_name); ?>(貯水率)</h1>
<hr/>
<dl class="compact">
    <dt>単位</dt>
    <dd><?php eh($percentage_unit);?></dd>
    <dt>最新</dt>
    <dd><?php measured_value($latest['percentage_value'], $latest['percentage_flags']); ?> <?php eh($percentage_unit); ?> (<?php eh($latest['measured_at'])?>)</dd>
</dl>
<hr/>
<table>
    <tbody>
<?php $prev_date = null; ?>
<?php foreach ($measured_data as $row):
    $difference = 0;
    if (is_measured_value_enable($row['percentage_value'], $row['percentage_flags'])) {
        if (is_measured_value_enable($measured_data_last['percentage_value'], $measured_data_last['percentage_flags'])) {
            $difference = $row['percentage_value'] - $measured_data_last['percentage_value'];
        }
        $measured_data_last = $row;
    } ?>
        <tr>
            <td><?php if ($prev_date !== $row['measured_date']): eh($row['measured_date']); $prev_date = $row['measured_date']; endif; ?></td>
            <td><?php eh($row['measured_time']); ?></td>
            <td><?php if (0 < $difference): ?><span class="increase"><?php endif; measured_value($row['percentage_value'], $row['percentage_flags']); if (0 < $difference): ?></span><?php endif; ?></td>
            <td><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?><span class="increse">↑</span><?php else: ?>→<?php endif; ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>