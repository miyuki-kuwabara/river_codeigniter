USE `LA61037255-frograindrop`;

DROP TABLE IF EXISTS `river_measured_data`;
DROP TABLE IF EXISTS `river_measure_values_views`;
DROP TABLE IF EXISTS `river_views`;
DROP TABLE IF EXISTS `river_measure_values`;
DROP TABLE IF EXISTS `river_measure_sources`;

CREATE TABLE `river_measure_sources` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ソースID',
  `name` VARCHAR(20) NOT NULL COMMENT 'ソース名',
  `type` TINYINT NOT NULL COMMENT 'ソース種別(1: 国土交通省水位、2: 国土交通省ダム、3: 和歌山県水位、4: 和歌山県ダム流入、5: 和歌山県ダム放流、6: 和歌山県ダム貯水位、7: 和歌山県ダム貯水量、8: 南郷洗堰)',
  `uri` VARCHAR(255) NOT NULL COMMENT 'データ取得元URI',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

CREATE TABLE `river_measure_values` (
  `measure_value_id` INT NOT NULL COMMENT '測定値ID',
  `measure_source_id` INT NOT NULL COMMENT 'ソースID',
  `type` TINYINT NOT NULL COMMENT 'データ種別(1: 水位、2: ダム流入、3: ダム放流、4: 貯水率)',
  `name` VARCHAR(12) NOT NULL COMMENT '測定値名',
  `unit` VARCHAR(8) NULL COMMENT '単位',
  `link_uri` VARCHAR(45) NULL COMMENT '水位集計ページリンク用URL（データ取得元と別に指定する場合）',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`measure_value_id`),
  UNIQUE KEY (`measure_source_id`, `type`)
);
CREATE INDEX `idex_river_measure_value_1`
  ON `river_measure_values` (`measure_source_id`, `type`, `name`, `unit`, `link_uri`);
ALTER TABLE `river_measure_values`
ADD CONSTRAINT FOREIGN KEY (`measure_source_id`) REFERENCES `river_measure_sources` (`id`);

CREATE TABLE `river_views` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'ビューID',
  `name` VARCHAR(45) NOT NULL COMMENT 'ビュー名',
  `keyword` VARCHAR(12) NOT NULL UNIQUE COMMENT 'キーワード',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;
CREATE INDEX `idex_river_views_1`
  ON `river_views` (`keyword`, `id`, `name`);

CREATE TABLE `river_measure_values_views` (
  `view_id` INT NOT NULL COMMENT 'ビューID',
  `measure_value_id` INT NOT NULL COMMENT '測定値ID',
  `sort_order` INT NOT NULL COMMENT '表示順',
  `created_at` DATETIME NOT NULL COMMENT '作成日時',
  `modified_at` DATETIME NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`view_id`, `measure_value_id`))
ENGINE = InnoDB;
CREATE INDEX `ideriver_measure_sourcesriver_measure_sourcesx_river_measure_values_views_1`
  ON `river_measure_values_views` (`view_id`, `measure_value_id`, `sort_order`);

CREATE TABLE IF NOT EXISTS `river_measured_data` (
  `measure_source_id` INT NOT NULL COMMENT '測定値ID',
  `measured_at` DATETIME NOT NULL COMMENT '測定日時',
  `value_type` TINYINT NOT NULL COMMENT 'データ種別(1: 水位、2: ダム流入、3: ダム放流、4: 貯水率)',
  `value` DECIMAL(10,3) NULL COMMENT '測定値',
  `flags` TINYINT NULL DEFAULT NULL COMMENT '1:暫定値, 2:欠測, 3:閉局, 4:未登録',
  `acquired_at` DATETIME NOT NULL COMMENT '収集日時',
  PRIMARY KEY (`measure_source_id`, `measured_at`, `value_type`))
ENGINE = InnoDB;
CREATE INDEX `idex_river_measured_data_1`
  ON `river_measured_data` (`measured_at`, `measure_source_id`, `value_type`, `value`, `flags`);
