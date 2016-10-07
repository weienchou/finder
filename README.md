# finder 使用說明
*	使用 GET 傳入參數
*	參數 keyword 
	關鍵字，字串
*	參數 limit
	限制筆數，數字
	預設 -1 無限制
	傳入非數字，使用預設

*	取得商品完整網址
	將 table `finder_type` column `ftdetial_url` 裡的 {$data}
	replace 成 table `finder_goods` column `fgsid` 

## PCHOME 
	`ec_pchome.php`
	

### 取得 商品以及商品類別

SELECT 
	`finder_type`.`ftname` AS `ec_name`,
	`finder_goods`.`fgsid` AS `woods_id`, 
	group_concat(`finder_category`.`fcname` SEPARATOR ' | ') AS `woods_category`, 
	`finder_goods`.`fgname` AS `woods_name`, 
	`finder_goods`.`fgoffer` AS `woods_price`,
	REPLACE (`finder_type`.`ftdetial_url`, '{$data}', `finder_goods`.`fgsid`) AS `woods_url`, 
	`finder_goods`.`fgpic_url` AS `woods_pic`

FROM `finder_goods`

INNER JOIN `finder_type`
	ON (`finder_goods`.`fgtype` = `finder_type`.`ftuid`)

LEFT JOIN `finder_relation` ON (`finder_relation`.`fcrgoods_uid` = `finder_goods`.`fguid`)

LEFT JOIN `finder_category` ON (`finder_category`.`fcuid` = `finder_relation`.`fcrcategory_uid`)

GROUP BY `finder_goods`.`fguid`

ORDER BY `finder_goods`.`fgupdate_time` DESC;