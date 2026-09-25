-- 水資源機構 池田総合管理所は「吉野川上流総合管理所」への改称・統合により
-- 旧サイト(ikesou.jp)が廃止(301→404)されたため、収集元を新サイトに変更する。
-- 対象: river_measure_sources の type 14(水資源機構 池田総合管理所)

UPDATE river_measure_sources
SET uri = 'https://www.water.go.jp/mizu/ikeda/mizuinfo/dyn/html/p0302/60/p030202.html',
    modified_at = NOW()
WHERE id = 31 AND type = 14; -- 下名

-- river_measure_values.link_uri も旧URLを指したままだった。新URLはPC向けの
-- 多機能なダッシュボードページ(局選択・ページング等あり)のため、Wakayamaの
-- 時と同様に直リンクはせずNULLに戻し、自前の蓄積データ画面に誘導する。

UPDATE river_measure_values
SET link_uri = NULL,
    modified_at = NOW()
WHERE id = 41 AND measure_source_id = 31; -- 吉野|下名
