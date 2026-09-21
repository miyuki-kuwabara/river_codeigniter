-- 京都府 河川防災情報のPC向けページ、時間毎の水位提供が終了したため
-- 収集元をスマートフォン向けページに切り替える。
-- 対象: river_measure_sources の type 12(京都府 河川防災情報)

UPDATE river_measure_sources
SET uri = 'https://chisuibousai.pref.kyoto.jp/sp/status/river_log_1_38.html',
    modified_at = NOW()
WHERE id = 28 AND type = 12; -- 周山

-- river_measure_values.link_uri も同じ新URLに更新する。
-- 新URLはスマートフォン向けページであり、一覧画面(view_list.php)からの
-- 直リンク先としてもそのまま利用できるため、Wakayamaのケースとは異なり
-- NULLには戻さない。

UPDATE river_measure_values
SET link_uri = 'https://chisuibousai.pref.kyoto.jp/sp/status/river_log_1_38.html',
    modified_at = NOW()
WHERE id = 38 AND measure_source_id = 28; -- 上桂|周山
