<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php eh($measure_source_name); ?> | 水位集計</title>
    <link rel="stylesheet" type="text/css" href="<?php eh(base_url('style.css')); ?>" />
</head>
<body>
<?php 
switch ($measure_source_type) {
case \Entities\MeasuredSourceTypes::MLITT_LEVEL:                 // 国土交通省水位
    include('values/water_level.php');
    break;
case \Entities\MeasuredSourceTypes::MLITT_DAM:                   // 国土交通省ダム
    include('values/mlitt_dam.php');
    break;
case \Entities\MeasuredSourceTypes::WAKAYAMA_LEVEL:              // 和歌山県水位
    include('values/water_level.php');
    break;
case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_INFLOW:         // 和歌山県ダム流入
    include('values/inflow.php');
    break;
case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_OUTFLOW:        // 和歌山県ダム放流
    include('values/outflow.php');
    break;
case \Entities\MeasuredSourceTypes::ARAIZEKI:                    // 南郷洗堰
    include('values/outflow.php');
    break;
case \Entities\MeasuredSourceTypes::NARA_LEVEL:                  // 奈良県河川情報システム水位
    include('values/water_level.php');
    break;
case \Entities\MeasuredSourceTypes::GIFU_LEVEL:                  // 岐阜県川の防災情報水位
    include('values/water_level.php');
    break;
case \Entities\MeasuredSourceTypes::AICHI_LEVEL:                 // 愛知県 川の防災情報水位
    include('values/water_level.php');
    break;
case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_STORAGE_LEVEL:  // 和歌山県ダム貯水位(予約)
case \Entities\MeasuredSourceTypes::WAKAYAMA_DAM_STORAGE_VOLUME: // 和歌山県ダム貯水量(予約)
default:
    break;
}
?>
</body>
</html>