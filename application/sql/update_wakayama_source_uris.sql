-- 和歌山県携帯サイト廃止に伴う、水位・ダムソースURIの更新
-- 対象: river_measure_sources の type 3(水位)/4(ダム流入)/5(ダム放流)
-- 新URIのccdは、新サイトの地点マスター(chitenconfig/waka_suii_st.csv, chitenconfig/dm_set.csv)で
-- 観測所名・ダム名を突き合わせて確認済み。

UPDATE river_measure_sources
SET uri = 'https://kasensabo01.pref.wakayama.lg.jp/suiiDetail.html?ccd=406',
    modified_at = NOW()
WHERE id = 5 AND type = 3; -- 金屋

UPDATE river_measure_sources
SET uri = 'https://kasensabo01.pref.wakayama.lg.jp/suiiDetail.html?ccd=503',
    modified_at = NOW()
WHERE id = 19 AND type = 3; -- 龍神

UPDATE river_measure_sources
SET uri = 'https://kasensabo01.pref.wakayama.lg.jp/damDetail.html?ccd=550',
    modified_at = NOW()
WHERE id = 3 AND type = 4; -- 椿山ダム流入

UPDATE river_measure_sources
SET uri = 'https://kasensabo01.pref.wakayama.lg.jp/damDetail.html?ccd=550',
    modified_at = NOW()
WHERE id = 4 AND type = 5; -- 椿山ダム放流

-- river_measure_values.link_uri が非NULLだと一覧(view_list.php)からその外部URLへ
-- 直接リンクしてしまう(NULLなら自前の蓄積データ画面 view/values/{id} にリンクする)。
-- 表示ページはスマホ/ガラケー向け想定のため、PC向けの新ページへのリンクは不適切。
-- 以下4件はいずれも上記と同じ理由で古い携帯サイトを指したままなのでNULLに戻す。

UPDATE river_measure_values
SET link_uri = NULL,
    modified_at = NOW()
WHERE id = 3 AND measure_source_id = 3; -- 日高|椿入

UPDATE river_measure_values
SET link_uri = NULL,
    modified_at = NOW()
WHERE id = 4 AND measure_source_id = 4; -- 日高|椿放

UPDATE river_measure_values
SET link_uri = NULL,
    modified_at = NOW()
WHERE id = 5 AND measure_source_id = 5; -- 有田|金屋

UPDATE river_measure_values
SET link_uri = NULL,
    modified_at = NOW()
WHERE id = 27 AND measure_source_id = 19; -- 日高|龍神
