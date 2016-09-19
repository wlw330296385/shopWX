-- ----------------------------
--  Table structure for `wfx_jam_config`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `wfx_jam_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `astime` date NOT NULL COMMENT '开始日期',
  `aetime` date NOT NULL COMMENT '结束日期',
  `actived` tinyint(1) NOT NULL DEFAULT '1',
  `neednum` int(11) NOT NULL DEFAULT '38' COMMENT '所需人数',
  `edstime` time NOT NULL COMMENT '开始时间',
  `edetime` time NOT NULL COMMENT '结束时间',
  `eachhour` int(11) NOT NULL COMMENT '每个小时多少瓶',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `wfx_jam_config`
-- ----------------------------
INSERT INTO `wfx_jam_config` VALUES ('1', '2016-06-23', '2016-06-30', '1', '1', '16:00:00', '23:59:59', '2');

CREATE TABLE IF NOT EXISTS `wfx_jam_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vipid` int(11) NOT NULL,
  `atime` int(11) NOT NULL,
  `iswin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_PID` (`vipid`),
  KEY `IDX_ISWIN_PID` (`iswin`,`vipid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;