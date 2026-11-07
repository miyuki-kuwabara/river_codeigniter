-- 国土交通省 水文水質データベースの仕様変更で旧CGI(DspWaterData.exe/DspDamData.exe)が
-- 廃止されたため、収集元を川の防災情報の観測所ページに切り替える。
-- 新しい水文水質データベースはrobots.txtで/search/の機械的な取得を許可していない。
-- MlittCollectorはこのURLのクエリから観測所を特定し、ページが読み込むJSONを取得する。
-- 対象: river_measure_sources の type 1(国土交通省水位)、type 2(国土交通省ダム)

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22087&obsCd=13&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 2 AND type = 1; -- 五條

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=6657&obsCd=71&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 6 AND type = 1; -- 田歌

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22068&obsCd=6&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 7 AND type = 1; -- 保津峡

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22061&obsCd=2&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 8 AND type = 1; -- 板波

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21811&obsCd=6&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 9 AND type = 1; -- 美濃

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21811&obsCd=17&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 11 AND type = 1; -- 今渡

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=7&ofcCd=21801&obsCd=7&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 12 AND type = 2; -- 蓮ダム

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21801&obsCd=26&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 13 AND type = 1; -- 田引

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=7&ofcCd=22552&obsCd=4&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 14 AND type = 2; -- 早明浦ダム

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22552&obsCd=3&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 15 AND type = 1; -- 豊永

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=7&ofcCd=22552&obsCd=7&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 16 AND type = 2; -- 池田ダム

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=7&ofcCd=22067&obsCd=1&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 18 AND type = 2; -- 天ヶ瀬ダム

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21811&obsCd=9&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 20 AND type = 1; -- 下洞戸

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21811&obsCd=2&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 22 AND type = 1; -- 上呂

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=21053&obsCd=15&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 25 AND type = 1; -- 宮宿

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=7&ofcCd=22077&obsCd=6&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 26 AND type = 2; -- 大滝ダム

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22077&obsCd=11&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 27 AND type = 1; -- 高見

UPDATE river_measure_sources
SET uri = 'https://www.river.go.jp/kawabou/pcfull/tm?itmkndCd=4&ofcCd=22551&obsCd=14&isCurrent=true&fld=0',
    modified_at = NOW()
WHERE id = 30 AND type = 1; -- 和食
