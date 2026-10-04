-- 和歌山県ダムに二川ダム(有田川)を追加する
-- 前提: merge_wakayama_dam_sources.sql 適用済み(type 15: 和歌山県ダム)
-- 一覧(view_id 1)では「有田|金屋」の直前に「有田|二ダ」(放流量)を表示する。

START TRANSACTION;

INSERT INTO river_measure_sources (name, type, uri, extra_string, created_at, modified_at)
VALUES ('二川ダム', 15, 'https://kasensabo01.pref.wakayama.lg.jp/damDetail.html?ccd=470', NULL, NOW(), NOW());
SET @source_id = LAST_INSERT_ID();

INSERT INTO river_measure_values (measure_source_id, value_type, name, unit, link_uri, created_at, modified_at)
VALUES (@source_id, 3, '有田|二ダ', 'm3/s', NULL, NOW(), NOW());
SET @value_id = LAST_INSERT_ID();

-- 「有田|金屋」の表示順を取得し、それ以降を1つずつ後ろにずらして空いた位置に挿入する
SELECT values_views.sort_order INTO @sort_order
FROM river_measure_values_views values_views
INNER JOIN river_measure_values measure_values ON values_views.measure_value_id = measure_values.id
WHERE values_views.view_id = 1 AND measure_values.name = '有田|金屋';

UPDATE river_measure_values_views
SET sort_order = sort_order + 1,
    modified_at = NOW()
WHERE view_id = 1 AND sort_order >= @sort_order;

INSERT INTO river_measure_values_views (view_id, measure_value_id, sort_order, created_at, modified_at)
VALUES (1, @value_id, @sort_order, NOW(), NOW());

COMMIT;
