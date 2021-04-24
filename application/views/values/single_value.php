<?php 
$value_key = "{$value_type}_value";
$flags_key = "{$value_type}_flags";
$unit_varname = "{$value_type}_unit";

// 最新の有効なデータ
$latest = null;
foreach ($measured_data as $row) {
    if (is_measured_value_enable($row[$value_key], $row[$flags_key])) {
        $latest = $row;
        break;
    }
}
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
<?php
if (!empty($measured_data)) {
    // センチネルの追加
    if ($measure_source_type == \Entities\MeasuredSourceTypes::ARAIZEKI) {
        // 南郷洗堰については最古のデータも表示対象にするため、センチネルを別途追加する
        $measured_data[] = array_reduce(
            array_keys($measured_data[0]),
            function ($a, $key) {
                $a[$key] = null;
            }, array('end' => 1));
    } else {
        // それ以外の場合は、最古のデータは次データに増減を表示するための捨てデータとなるため、センチネルのフラグのみ立てる
        $measured_data[count($measured_data) - 1]['end'] = 1;
    }
}

$prev_date = null;
$output = array();
// 測定日時順で降順に表示するが、過去データとの増減を出力する必要がある。、
// このため、過去データに向かって遡り、有効な測定値か最古のデータが出てきたら
// そこまでの測定値を出力する。
foreach ($measured_data as $row) :
    $last_enable = null;
    if (is_measured_value_enable($row[$value_key], $row[$flags_key])):
        $last_enable = $row[$value_key];
    endif;
    if (isset($row['end']) || isset($last_enable)):
        foreach ($output as $current):
            $difference = 0;
            if (isset($last_enable) &&
                is_measured_value_enable($current[$value_key], $current[$flags_key])):
                $difference = $current[$value_key] - $last_enable;
            endif; ?>
        <tr>
            <td><?php if ($prev_date !== $current['measured_date']): eh($current['measured_date']); $prev_date = $current['measured_date']; endif; ?></td>
            <td><?php eh($current['measured_time']); ?></td>
            <td<?php if (0 < $difference): ?> class="increase"<?php endif; ?>><?php measured_value($current[$value_key], $current[$flags_key], $decimal); ?></td>
            <td<?php if (0 < $difference): ?> class="increase"<?php endif; ?>><?php if ($difference < 0): ?>↓<?php elseif (0 < $difference): ?>↑<?php else: ?>→<?php endif; ?></td>
        </tr>
<?php   endforeach;
        $output = array();
    endif;
    $output[] = $row;
endforeach; ?>
    </tbody>
</table>