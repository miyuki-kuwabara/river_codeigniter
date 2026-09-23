-- 南郷洗堰の収集元を、正確な時刻付き毎時データが取れるJSON APIに変更する。
-- 対象: river_measure_sources の type 8(南郷洗堰)

UPDATE river_measure_sources
SET uri = 'http://biwako-mizukanri.jp/daminfo1_h.json',
    modified_at = NOW()
WHERE id = 17 AND type = 8; -- 南郷洗堰
