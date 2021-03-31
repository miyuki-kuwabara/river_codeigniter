USE `LA61037255-frograindrop`;

DROP TABLE IF EXISTS `river_measure_sources`;
CREATE TABLE `river_measure_sources` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ソースID',
  `name` VARCHAR(20) NOT NULL COMMENT 'ソース名',
  `type` TINYINT NOT NULL COMMENT 'ソース種別(1: 国土交通省水位、2: 国土交通省ダム、3: 和歌山県水位、4: 和歌山県ダム流入、5: 和歌山県ダム放流、6: 和歌山県ダム貯水位、7: 和歌山県ダム貯水量、8: 南郷洗堰)',
  `uri` VARCHAR(255) NOT NULL COMMENT 'データ取得元URI',
  `is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT '削除フラグ(1: 削除済み)',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `river_measure_values`;
CREATE TABLE `river_measure_values` (
  `measure_source_id` INT NOT NULL COMMENT 'ソースID',
  `type` TINYINT NOT NULL COMMENT 'データ種別(1: 水位、2: ダム流入、3: ダム放流、4: 貯水率)',
  `name` VARCHAR(12) NOT NULL COMMENT '測定値名',
  `unit` VARCHAR(8) NULL COMMENT '単位',
  `link_uri` VARCHAR(45) NULL COMMENT '水位集計ページリンク用URL（データ取得元と別に指定する場合）',
  `is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT '削除フラグ(1: 削除済み)',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`measure_source_id`, `type`)
);

DROP TABLE IF EXISTS `river_views`;
CREATE TABLE `river_views` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ビューID',
  `name` VARCHAR(45) NOT NULL COMMENT 'ビュー名',
  `keyword` VARCHAR(12) NOT NULL UNIQUE COMMENT 'キーワード',
  `is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT '削除フラグ(1: 削除済み)',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `river_measure_sources_views`;
CREATE TABLE `river_measure_sources_views` (
  `view_id` INT NOT NULL COMMENT 'ビューID',
  `measure_source_id` INT NOT NULL COMMENT '測定値ID',
  `measure_value_type` TINYINT NOT NULL COMMENT 'データ種別(1: 水位、2: ダム流入、3: ダム放流、4: 貯水率)',
  `sort_order` INT NOT NULL COMMENT '表示順',
  `is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT '削除フラグ(1: 削除済み)',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`view_id`, `measure_source_id`, `measure_value_type`))
ENGINE = InnoDB;

DROP TABLE IF EXISTS `river_measured_data`;
CREATE TABLE IF NOT EXISTS `river_measured_data` (
  `measure_source_id` INT NOT NULL COMMENT '測定値ID',
  `measured_at` DATETIME NOT NULL COMMENT '測定日時',
  `value_type` TINYINT NOT NULL COMMENT 'データ種別(1: 水位、2: ダム流入、3: ダム放流、4: 貯水率)',
  `value` DECIMAL(10,3) NULL COMMENT '測定値',
  `flags` TINYINT NULL DEFAULT NULL COMMENT '1:暫定値, 2:欠測, 3:閉局, 4:未登録',
  `is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT '削除フラグ(1: 削除済み)',
  `acquired_at` DATETIME NOT NULL COMMENT '収集日時',
  PRIMARY KEY (`measure_source_id`, `measured_at`, `value_type`))
ENGINE = InnoDB;

