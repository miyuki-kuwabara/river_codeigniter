<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>水位集計</title>
    <style type="text/css">
    td {text-align: right;}
    .increase {color: red;}
    </style>
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
<?php foreach ($list as $measure_value) : ?>
            <tr>
                <td><?php echo $measure_value['name']; ?></td>
<?php   foreach ($measure_value['values'] as $measured_at => $value) :?>
                <td><?php
                if (0 < $value['difference']): ?><span class="increase"><?php endif;
                     echo is_null($value['value']) ? '--.--' : sprintf('%.2f', $value['value']);
                if (0 < $value['difference']):?></span><?php endif; ?></td>
<?php   endforeach;?>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <hr/>
</body>
</html>