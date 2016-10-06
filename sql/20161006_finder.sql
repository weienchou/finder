# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.25)
# Database: finder
# Generation Time: 2016-10-06 09:26:20 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table finder_category
# ------------------------------------------------------------

DROP TABLE IF EXISTS `finder_category`;

CREATE TABLE `finder_category` (
  `fcuid` varchar(32) NOT NULL DEFAULT '' COMMENT '類別編號',
  `fcsid` varchar(255) NOT NULL DEFAULT '' COMMENT '網站類別編號',
  `fcname` varchar(40) NOT NULL DEFAULT '' COMMENT '類別名稱',
  `fcupdate_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `fccreate_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '類別建立時間',
  PRIMARY KEY (`fcuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table finder_goods
# ------------------------------------------------------------

DROP TABLE IF EXISTS `finder_goods`;

CREATE TABLE `finder_goods` (
  `fguid` varchar(32) NOT NULL DEFAULT '' COMMENT '商品編號',
  `fgsid` varchar(40) NOT NULL DEFAULT '' COMMENT '網站商品編號',
  `fgname` varchar(255) NOT NULL DEFAULT '' COMMENT '商品名稱',
  `fgprice` int(11) NOT NULL COMMENT '商品價格',
  `fgoffer` int(11) NOT NULL COMMENT '商品特價價格',
  `fgpic_url` varchar(255) NOT NULL,
  `fgtype` varchar(32) NOT NULL DEFAULT '' COMMENT '商品網站',
  `fgupdate_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '商品更新時間',
  `fgcreate_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '商品建立時間',
  PRIMARY KEY (`fguid`),
  KEY `fgtype` (`fgtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table finder_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `finder_log`;

CREATE TABLE `finder_log` (
  `fluid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `flurl` varchar(255) NOT NULL DEFAULT '' COMMENT '抓取網址',
  `flpost` text NOT NULL COMMENT 'request post',
  `flheader` text NOT NULL COMMENT '抓取回應標頭',
  `fldata` text NOT NULL COMMENT '抓取內容',
  `fltime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '抓取時間',
  PRIMARY KEY (`fluid`)
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8;



# Dump of table finder_relation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `finder_relation`;

CREATE TABLE `finder_relation` (
  `fcrcategory_uid` varchar(32) NOT NULL DEFAULT '' COMMENT '類別編號',
  `fcrgoods_uid` varchar(32) NOT NULL DEFAULT '' COMMENT '商品編號',
  `fcrcreate_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
  PRIMARY KEY (`fcrcategory_uid`,`fcrgoods_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table finder_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `finder_type`;

CREATE TABLE `finder_type` (
  `ftuid` varchar(32) NOT NULL DEFAULT '' COMMENT '網站編號',
  `ftname` varchar(10) NOT NULL DEFAULT '' COMMENT '網站名稱',
  `ftdetial_url` varchar(255) NOT NULL COMMENT '商品詳細網址',
  `ftgetwoods_url` varchar(255) NOT NULL COMMENT '商品搜尋網址',
  `ftgetmaincategory_url` varchar(255) NOT NULL DEFAULT '' COMMENT '商品類別網址',
  `ftgetcategory_url` varchar(255) NOT NULL DEFAULT '' COMMENT '商品類別網址',
  `ftgetpic_url` varchar(255) NOT NULL COMMENT '商品照片網址',
  `ftsleep_time` tinyint(4) NOT NULL DEFAULT '5' COMMENT 'delay 時間',
  `ftcreate_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間',
  PRIMARY KEY (`ftuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `finder_type` WRITE;
/*!40000 ALTER TABLE `finder_type` DISABLE KEYS */;

INSERT INTO `finder_type` (`ftuid`, `ftname`, `ftdetial_url`, `ftgetwoods_url`, `ftgetmaincategory_url`, `ftgetcategory_url`, `ftgetpic_url`, `ftsleep_time`, `ftcreate_time`)
VALUES
	('005d9e2bdbd780f0906bcdda98a1679d','UDN','http://shopping.udn.com/mall/cus/search/DetailAction.do?action=Cc1c02.do&dc_cargxuid_0={$data}','http://shopping.udn.com/mall/cus/search/SearchAction.do?keyword={$data}&cid=&sort=weight&pageSize={$pageno}&start={$page}&key={$key}','http://shopping.udn.com/mall/cus/search/SearchAjaxService.inw?method=cateTreeview&keyword={$data}&cid={$category}','','',1,'2016-08-01 15:35:27'),
	('06c56a89949d617def52f371c357b6db','MOMO','http://m.momoshop.com.tw/goods.momo?i_code={$data}','http://m.momoshop.com.tw/mosearch/searchEg.jsp','','http://m.momoshop.com.tw/mosearch/searchEg.jsp','http://img3.momoshop.com.tw/goodsimg/{$data}',1,'2016-07-07 10:48:03'),
	('11fe66a87df42ed3de1be8af0f3f33bd','PCHOME','http://24h.pchome.com.tw/prod/{$data}','http://ecshweb.pchome.com.tw/search/v3.3/all/results?q={$data}&page={$page}&sort=rnk/dc','http://ecshweb.pchome.com.tw/search/v3.3/all/categories?q={$data}','http://ecapi.pchome.com.tw/ecshop/cateapi/v1.4/store&id={$data}&fields=Id,Name&_callback=jsonpcb_store','http://a.ecimg.tw/{$data}',3,'2016-07-04 14:19:36'),
	('3dd13b4e40b8fc1922224747295119d4','UMALL','http://www.u-mall.com.tw/Pages/Prod.aspx?sc={$data}','http://www.u-mall.com.tw/Pages/mobile_search.aspx','','','',2,'0000-00-00 00:00:00'),
	('5118d9084cd0035a424a1668e35e5051','ETMALL','http://www.etmall.com.tw/Pages/ProductDetail.aspx?ProductSKU={$data}','http://www.etmall.com.tw/Pages/mobile_search.aspx?SearchKeyword={$data}&ProductPage={$page}&RecordsPerPage={$pageno}','','','',2,'2016-08-09 15:19:01'),
	('c0f95b42a2dc26ee16f357c9d8c673e1','GOHAPPY','http://www.gohappy.com.tw/ec2/search?sid=&hotNum=0&search={$data}','http://m.gohappy.com.tw/smartphone/SearchAdv.do?display=2&pageindex={$page}&pageno={$pageno}&searchFlag=firstSearch&firstTime=0&search={$data}','http://m.gohappy.com.tw/smartphone/SearchAdv.do?searchs={$data}&sids={$cate_cid}&pageindex={$page}&cateLvs={$lv}&cid={$cate}&pageno={$pageno}','','http://img.gohappy.com.tw/images/product/{$data}',2,'2016-07-25 11:53:57'),
	('ef351e74c34933ec060bc62209516818','YAHOO','https://tw.buy.yahoo.com/gdsale/gdsale.asp?gdid={$data}','https://tw.search.buy.yahoo.com/search/shopping/product?p={$data}&qt=product&clv=0&property=shopping&sub_property=shopping&srch=product&act=gdsearch&pg={$page}&dlv=0&rescheck=1','https://tw.search.buy.yahoo.com/search/shopping/{$data}','https://tw.search.buy.yahoo.com/search/shopping/product?p={$data}&qt=product&cid={$category}&clv=1&property=shopping&sub_property=shopping&srch=product&pg={$page}&act=gdsearch','https://s.yimg.com/wb/images/{$data}',1,'2016-07-18 13:39:39');

/*!40000 ALTER TABLE `finder_type` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
