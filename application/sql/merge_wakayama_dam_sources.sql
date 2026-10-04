-- 和歌山県ダムの流入・放流ソースを、国土交通省ダム同様に1ソースへ統合する
-- 対象: 椿山ダム (river_measure_sources id 3: 流入(type 4), id 4: 放流(type 5))
-- 統合後は id 3 を type 15(和歌山県ダム)とし、流入量・放流量・貯水量をまとめて収集する。
-- 一覧の「日高|椿入」「日高|椿放」はそのまま残り、どちらも統合後の詳細画面にリンクする。

START TRANSACTION;

UPDATE river_measure_sources
SET name = '椿山ダム',
    type = 15,
    modified_at = NOW()
WHERE id = 3 AND type = 4; -- 椿山ダム流入 → 椿山ダム

-- 日高|椿放 の参照先を統合後のソースに付け替える
UPDATE river_measure_values
SET measure_source_id = 3,
    modified_at = NOW()
WHERE id = 4 AND measure_source_id = 4 AND value_type = 3;

-- 蓄積済みの放流量も引き継ぐ(ソース3は流入量のみのため主キーは衝突しない)
UPDATE river_measured_data
SET measure_source_id = 3
WHERE measure_source_id = 4 AND value_type = 3;

DELETE FROM river_measured_data
WHERE measure_source_id = 4;

DELETE FROM river_measure_sources
WHERE id = 4 AND type = 5; -- 椿山ダム放流

COMMIT;
