<?php
namespace Entities {
    class MeasuredSourceTypes
    {
        const MLITT_LEVEL = 1;                  // 国土交通省水位
        const MLITT_DAM = 2;                    // 国土交通省ダム
        const WAKAYAMA_LEVEL = 3;               // 和歌山県水位
        const WAKAYAMA_DAM_INFLOW = 4;          // 和歌山県ダム流入
        const WAKAYAMA_DAM_OUTFLOW = 5;         // 和歌山県ダム放流
        const WAKAYAMA_DAM_STORAGE_LEVEL = 6;   // 和歌山県ダム貯水位(予約)
        const WAKAYAMA_DAM_STORAGE_VOLUME = 7;  // 和歌山県ダム貯水量(予約)
        const ARAIZEKI = 8;                     // 南郷洗堰
        const NARA_LEVEL = 9;                   // 奈良県河川情報システム水位
        const GIFU_LEVEL = 10;                  // 岐阜県川の防災情報水位
        const AICHI_LEVEL = 11;                 // 愛知県 川の防災情報水位
    }
}
