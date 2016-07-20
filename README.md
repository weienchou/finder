# finder

#YAHOO
1. 每頁 48 筆
2. 

# 取得 商品以及商品類別
SELECT `finder_goods`.`fgname`, group_concat(`finder_category`.`fcname` SEPARATOR ' | ') AS category

FROM `finder_goods`

INNER JOIN `finder_relation`
	ON (`finder_relation`.`fcrgoods_uid` = `finder_goods`.`fguid`)
	
INNER JOIN `finder_category`
	ON (`finder_category`.`fcuid` = `finder_relation`.`fcrcategory_uid`)
	
GROUP BY `finder_goods`.`fguid`