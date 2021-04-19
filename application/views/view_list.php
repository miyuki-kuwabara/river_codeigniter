<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>水位集計</title>
    <style type="text/css">
    h1 {font-size: medium; font-weight: normal;}
    dl.compact { overflow: hidden; padding: 0; }
    dl.compact dt { float: left; clear: left; width: 10ex; }
    dl.compact dd { margin-left: 10ex; padding: 0;  }
    table {white-space: nowrap;}
    th {text-align:center; font-weight: normal; padding: 0.2em 1em;}
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
                <td><a href="<?php 
                if ($measure_value['link_uri'] === null) {
                    echo base_url("view/values/{$measure_value['measure_source_id']}");
                } else {
                    echo $measure_value['link_uri'];
                }
                ?>"><?php echo $measure_value['name']; ?></a></td>
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