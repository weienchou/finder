# finder 使用說明
*	使用 GET 傳入參數
*	參數 keyword 
	keyword 關鍵字，字串
*	參數 limit
	limit 關鍵字，數字
	預設 -1 無限制
	傳入非數字，使用預設

## 取得 商品以及商品類別
SELECT `finder_goods`.`fgname`, group_concat(`finder_category`.`fcname` SEPARATOR ' | ') AS category

FROM `finder_goods`

INNER JOIN `finder_relation`
	ON (`finder_relation`.`fcrgoods_uid` = `finder_goods`.`fguid`)
	
INNER JOIN `finder_category`
	ON (`finder_category`.`fcuid` = `finder_relation`.`fcrcategory_uid`)
	
GROUP BY `finder_goods`.`fguid`

ORDER BY `finder_goods`.`fgupdate_time` DESC