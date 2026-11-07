-- 奈良県河川情報システムがPHP脆弱性への対応で休止しているため、
-- 栄山寺の収集元を川の防災情報の観測所ページに切り替える。
-- 川の防災情報はMlittCollectorで収集するため、type も 9(奈良県河川情報システム水位)から
-- 1(国土交通省水位)に変更する。

UPDATE river_measure_sources
SET type = 1,
    uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=7425&obsCd=125&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 1 AND type = 9; -- 栄山寺
