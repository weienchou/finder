<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('etmall');											// 建立 Finder


$Finder->set_start_time();
if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $LoopKeyWord) {
	$TotalPage = 1;

	echo '<hr />'.date('Y/m/d H:i:s')." 關鍵字 {$LoopKeyWord}<br />";

	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品區
	 * --------------------------------------------------------------------------------------------------------------------
	 */

	for($CurrentPage = 1; $CurrentPage <= $TotalPage; $CurrentPage++) {

		echo date('Y/m/d H:i:s')." 第 {$CurrentPage} 頁，抓取中...<br />";

		$WoodsList = GetWoodsList($Finder, $LoopKeyWord, $CurrentPage);	// 開始抓資料

		$PageStatus = ParseWoodsPage($WoodsList);
		if($TotalPage == 1 && !empty($PageStatus)) {					//更新頁數
			$TotalWoods = $PageStatus['TotRows'];						// 總共有幾個可以抓
			$TotalPage = $PageStatus['TotRows'];
																		// 總共要抓幾頁

			echo date('Y/m/d H:i:s')." 共 {$TotalPage} 頁，限制 {$Finder->limit_woods} 筆。<br />";
		}

		if(!ParseWoodsList($Finder, $WoodsList)) break;					// 分析資料並儲存
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 確定 Woods ID
	 * --------------------------------------------------------------------------------------------------------------------
	 */

	if(isset($Finder->tmp_category['Woods']) && count($Finder->tmp_category['Woods']) > 0 ) {
		$WoodIDArray = $Finder->CheckWoodsIDInDB($Finder->tmp_category['Woods']);
	} else {
		echo date('Y/m/d H:i:s')." 沒有商品可以抓取。 <hr />";
		$Finder->set_stop_time();
		$Finder->show_time();
	}
	
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
			'/li id=\"([0-9]+)[\s\S]*?name\">([\s\S]*?)<\//', 
			$PageCode, 
			$ParseCategoryArray
		);

		if(count($ParseCategoryArray[0]) > 0) foreach($ParseCategoryArray[0] as $k => $v) {
			$Finder->create_relation($ParseCategoryArray[1][$k], md5($LoopWoods));
			$Finder->create_category($ParseCategoryArray[1][$k], $ParseCategoryArray[2][$k]);
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

function GetWoodsList($aFinder, $Keyword, $Page) {
	$woods_url = strtr($aFinder->current_type['ftgetwoods_url'], Array(
		'{$data}' => urlencode($Keyword),
		'{$page}' => $Page,
		'{$pageno}' => 20,
	));

	$str_woods_code = $aFinder->get_html_code($woods_url);

	return $str_woods_code;
}

function ParseWoodsPage($WoodsCode) {
	$ParseWoodsPageArray = array();
	preg_match(
		'/TotalCount\", [0-9]+, ([0-9]+),/', 
		$WoodsCode, 
		$ParseWoodsPageArray
	);

	if(count($ParseWoodsPageArray) != 2) return Array(
		"TotRows" => 0,
	);

	return Array(
		"TotRows" => $ParseWoodsPageArray[1]
	);
}

function ParseWoodsList($aFinder, $WoodsCode) {
	$ParseWoodsArray = array();
	preg_match_all(
		"/<li class=\\\".*left\\\">[\\s\\S]*?Good_ID = (.*)\\;[\\s\\S]*?DoubleToSingle\\(\\'(.*)\\'\\)[\\s\\S]*?Sys_showSearchPRCValue\\(\\'(.*?)\\', \\'([0-9]+)\\'\\)/", 
		$WoodsCode, 
		$ParseWoodsArray
	);

	if(count($ParseWoodsArray[0]) > 0) foreach($ParseWoodsArray[0] as $k => $v) {
		if($aFinder->current_limit_woods == $aFinder->limit_woods) {	// && $aFinder->limit_woods != -1 // 超過 limit
			echo date('Y/m/d H:i:s')." Out of limit.<hr />";
			return false;
		}
		$aFinder->create_woods(
			$ParseWoodsArray[1][$k], 
			$ParseWoodsArray[2][$k], 
			0, 
			$ParseWoodsArray[4][$k], 
			'', 
			CreateImageUrl($ParseWoodsArray[1][$k])
		);
		$aFinder->tmp_category['Woods'][] = $ParseWoodsArray[1][$k];
	}
	return true;
}

function CreateImageUrl($ID) {
	// $ID = substr($ID, 2);
	$input_line = sprintf('%09d', $ID);
	$ReturnUrl = 'http://media.etmall.com.tw/NXimg/'.substr($input_line, 0, 6).'/'.(int)$input_line.'/'.(int)$input_line.'_L.jpg';
	return $ReturnUrl;
}

function GetWoodsPageCode($aFinder, $WoodsID) {
	$woods_url = strtr($aFinder->current_type['ftdetial_url'], Array(
		'{$data}' => $WoodsID
	));

	return $aFinder->get_html_code($woods_url);
}