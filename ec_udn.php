<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('udn');											// 建立 Finder


$Finder->set_start_time();
if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $LoopKeyWord) {
	$TotalPage = 1;

	echo '<hr />'.date('Y/m/d H:i:s')." 關鍵字 {$LoopKeyWord}<br />";

	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 取得金鑰
	 * --------------------------------------------------------------------------------------------------------------------
	 */

	$PageSearchKey = GetWoodsList($Finder, $LoopKeyWord, 1, '');		// 金鑰
	$PageKey = ParseWoodsKey($PageSearchKey);

	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品區
	 * --------------------------------------------------------------------------------------------------------------------
	 */

	for($CurrentPage = 1; $CurrentPage <= $TotalPage; $CurrentPage++) {

		echo date('Y/m/d H:i:s')." 第 {$CurrentPage} 頁，抓取中...<br />";

		$WoodsList = GetWoodsList($Finder, $LoopKeyWord, $CurrentPage, $PageKey);	// 開始抓資料

		$PageStatus = ParseWoodsPage($WoodsList);

		if($TotalPage == 1 && !empty($PageStatus)) {					//更新頁數
			$TotalWoods = $PageStatus['TotRows'];						// 總共有幾個可以抓
			$TotalPage = $PageStatus['TotPage'];
																		// 總共要抓幾頁

			echo date('Y/m/d H:i:s')." 共 {$TotalPage} 頁，共 {$TotalWoods} 筆，限制 {$Finder->limit_woods} 筆。<br />";
		}

		if(!ParseWoodsList($Finder, $WoodsList)) break;					// 分析資料並儲存
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 確定 Woods ID
	 * --------------------------------------------------------------------------------------------------------------------
	 */
	
	$WoodIDArray = $Finder->CheckWoodsIDInDB($Finder->tmp_category['Woods']);
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品類別區
	 * --------------------------------------------------------------------------------------------------------------------
	 */
	
	echo date('Y/m/d H:i:s')." 抓取商品類別... <br />";

	if(count($WoodIDArray) > 0) foreach($WoodIDArray as $LoopWoods) {
		$PageCode = GetWoodsPageCode($Finder, $LoopWoods);

		$ParseCategoryArray = array();
		preg_match_all(
			'/id=\"([A-Z_0-9]+)\">([\s\S]*?)<\/a>/', 
			$PageCode, 
			$ParseCategoryArray
		);

		if(count($ParseCategoryArray[0]) > 0) foreach($ParseCategoryArray[0] as $k => $v) {

			if (preg_match("/^{$ParseCategoryArray[1][$k]}/", $Finder->repeat_category[$LoopWoods]) === 1) {
				$Finder->create_relation($ParseCategoryArray[1][$k], md5($LoopWoods));
				$Finder->create_category($ParseCategoryArray[1][$k], $ParseCategoryArray[2][$k]);
			}
		}
	}

	echo date('Y/m/d H:i:s')." 抓取商品類別結束。 <hr />";

}
$Finder->set_stop_time();
$Finder->show_time();

/*
 * --------------------------------------------------------------------------------------------------------------------
 * 函式區
 * --------------------------------------------------------------------------------------------------------------------
 */

function GetWoodsList($aFinder, $Keyword, $Page, $Key) {
	$woods_url = strtr($aFinder->current_type['ftgetwoods_url'], Array(
		'{$data}' => urlencode($Keyword),
		'{$page}' => $Page,
		'{$pageno}' => 20,
		'{$key}' => $Key,
	));

	$str_woods_code = $aFinder->get_html_code($woods_url);

	return $str_woods_code;
}

function ParseWoodsKey($WoodsCode) {
	$ParseWoodsPageArray = array();
	preg_match(
		'/name=\"key[\s\S]*?value=\"([\s\S]*?)\" \/>/', 
		$WoodsCode, 
		$ParseWoodsPageArray
	);

	if(count($ParseWoodsPageArray) != 2) return '';

	return $ParseWoodsPageArray[1];
}

function ParseWoodsPage($WoodsCode) {
	$ParseWoodsPageArray = array();
	preg_match(
		'/sort_label\"[\s\S]*?hlight\">([0-9]+)<\/[\s\S]*?\/span>\/([0-9]+)[\s\S]*?頁/', 
		$WoodsCode, 
		$ParseWoodsPageArray
	);
	// var_dump($ParseWoodsPageArray); die();

	if(count($ParseWoodsPageArray) != 3) return Array(
		"TotRows" => 0,
		"TotPage" => 0
	);

	return Array(
		"TotRows" => $ParseWoodsPageArray[1],
		"TotPage" => $ParseWoodsPageArray[2]
	);
}

function ParseWoodsList($aFinder, $WoodsCode) {
	$ParseWoodsArray = array();
	preg_match_all(
		"/<tr.*>[\\s\\S]*?<a href=\\\".*dc_cateid_0\\=([^\\&]*).*cargxuid.*\\=([0-9A-Z]+)\\&.*title=\\\"([^\\\"]*)\\\".*img src=\\\"([^\\\"]+)\\?.*\\\"[\\s\\S]*?pd_hlight\\\">([0-9]+)<\\/span>/", 
		$WoodsCode, 
		$ParseWoodsArray
	);

	if(count($ParseWoodsArray[0]) > 0) foreach($ParseWoodsArray[0] as $k => $v) {
		if($aFinder->current_limit_woods == $aFinder->limit_woods) {	// && $aFinder->limit_woods != -1 // 超過 limit
			echo date('Y/m/d H:i:s')." Out of limit.<hr />";
			return false;
		}
		$aFinder->create_woods(
			$ParseWoodsArray[2][$k], 
			$ParseWoodsArray[3][$k], 
			0, 
			$ParseWoodsArray[5][$k], 
			'', 
			'http:'.$ParseWoodsArray[4][$k]
		);
		$aFinder->tmp_category['Woods'][] = $ParseWoodsArray[2][$k];
		$aFinder->repeat_category[$ParseWoodsArray[2][$k]] = $ParseWoodsArray[1][$k];
	}
	return true;
}

function GetWoodsPageCode($aFinder, $WoodsID) {
	$woods_url = strtr($aFinder->current_type['ftdetial_url'], Array(
		'{$data}' => $WoodsID
	));

	return $aFinder->get_html_code($woods_url);
}