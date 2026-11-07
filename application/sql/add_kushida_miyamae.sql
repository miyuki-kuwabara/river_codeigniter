-- 櫛田川の宮前観測所を追加する
-- 川の防災情報はMlittCollectorで収集するため type は 1(国土交通省水位)とする。
-- 田引より下流にあるため、一覧(view_id 1: トップ、2: 中部)では「櫛田|田引」の直後に表示する。

START TRANSACTION;

INSERT INTO river_measure_sources (name, type, uri, extra_string, created_at, modified_at)
VALUES ('宮前', 1, 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=6145&obsCd=40&isCurrent=true&fld=0', NULL, NOW(), NOW());
SET @source_id = LAST_INSERT_ID();

INSERT INTO river_measure_values (measure_source_id, value_type, name, unit, link_uri, created_at, modified_at)
VALUES (@source_id, 1, '櫛田|宮前', 'm', NULL, NOW(), NOW());
SET @value_id = LAST_INSERT_ID();

-- 各一覧で「櫛田|田引」より後ろを1つずつ後ろにずらして空いた位置に挿入する
SELECT values_views.sort_order INTO @sort_order
FROM river_measure_values_views values_views
INNER JOIN river_measure_values measure_values ON values_views.measure_value_id = measure_values.id
WHERE values_views.view_id = 1 AND measure_values.name = '櫛田|田引';

UPDATE river_measure_values_views
SET sort_order = sort_order + 1,
    modified_at = NOW()
WHERE view_id = 1 AND sort_order > @sort_order;

INSERT INTO river_measure_values_views (view_id, measure_value_id, sort_order, created_at, modified_at)
VALUES (1, @value_id, @sort_order + 1, NOW(), NOW());

SELECT values_views.sort_order INTO @sort_order
FROM river_measure_values_views values_views
INNER JOIN river_measure_values measure_values ON values_views.measure_value_id = measure_values.id
WHERE values_views.view_id = 2 AND measure_values.name = '櫛田|田引';

UPDATE river_measure_values_views
SET sort_order = sort_order + 1,
    modified_at = NOW()
WHERE view_id = 2 AND sort_order > @sort_order;

INSERT INTO river_measure_values_views (view_id, measure_value_id, sort_order, created_at, modified_at)
VALUES (2, @value_id, @sort_order + 1, NOW(), NOW());

COMMIT;
