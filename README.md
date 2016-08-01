# finder 使用說明
*	使用 GET 傳入參數
*	參數 keyword 
	關鍵字，字串
*	參數 limit
	限制筆數，數字
	預設 -1 無限制
	傳入非數字，使用預設

## PCHOME
*	利用關鍵字搜尋，每搜尋一頁儲存商品，並儲存的商品類別。

## YAHOO
*	利用關鍵字搜尋，每搜尋一頁儲存商品，並依照取得的商品類別，進行深度搜索。
**	REGEX 可能有些問題＠＠

## MOMO
*	利用關鍵字搜尋，每搜尋一頁儲存商品，並依照取得的商品類別，進行深度搜索。
**	商品類別待優化。

## GOHAPPY
*	關鍵字 apple 一頁 20 筆，約 893 頁

*	利用關鍵字搜尋，將所有頁數的商品儲存，並儲存主要商品類別，再依照商品類別搜尋商品。
	

### 取得 商品以及商品類別
SELECT `finder_goods`.`fgsid`, group_concat(`finder_category`.`fcname` SEPARATOR ' | ') AS category, `finder_goods`.`fgname`, `finder_goods`.`fgoffer`

FROM `finder_goods`

LEFT JOIN `finder_relation`
	ON (`finder_relation`.`fcrgoods_uid` = `finder_goods`.`fguid`)
	
LEFT JOIN `finder_category`
	ON (`finder_category`.`fcuid` = `finder_relation`.`fcrcategory_uid`)
	
GROUP BY `finder_goods`.`fguid`

ORDER BY `finder_goods`.`fgupdate_time` DESC