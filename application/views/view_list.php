<?php
$decimals = array(
    \Entities\MeasuredValueTypes::WATER_LEVEL => 2,
    \Entities\MeasuredValueTypes::INFLOW => 2,
    \Entities\MeasuredValueTypes::OUTFLOW => 2,
    \Entities\MeasuredValueTypes::PERCENTAGE_OF_STORAGE => 1,
    \Entities\MeasuredValueTypes::AMOUNT_OF_STORAGE => 0,
);
?><!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>水位集計</title>
    <link rel="stylesheet" type="text/css" href="<?php eh(base_url('style.css')); ?>" />
</head>
<body>
    <table>
        <thead>
            <tr>
                <td><?php echo $first_date?></td>
<?php foreach ($times as $time) : ?>
                <td><?php echo $time; ?></td>
<?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
<?php foreach ($list as $measure_value) :
        $decimal = $decimals[$measure_value['value_type']]; ?>
            <tr>
                <td><a href="<?php 
        if ($measure_value['link_uri'] === null) {
            echo base_url("view/values/{$measure_value['measure_source_id']}");
        } else {
            echo $measure_value['link_uri'];
        } ?>"><?php eh($measure_value['name']); ?></a></td>
<?php   $last_value = array_shift($measure_value['values']); 
        foreach ($measure_value['values'] as $measured_at => $value):
            $difference = 0;
            if (is_measured_value_enable($last_value['value'], $last_value['flags'])) {
                if (is_measured_value_enable($last_value['value'], $last_value['flags'])) {
                    $difference = $value['value'] - $last_value['value'];
                }
                $last_value = $value;
            }?>
                <td<?php if (0 < $difference): ?> class="increase"<?php endif; ?>><?php measured_value($value['value'], $value['flags'], $decimal); ?></td>
<?php   endforeach;?>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <hr/>
</body>
</html>