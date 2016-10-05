<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('yahoo');											// 建立 Finder


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
			$TotalPage = ceil($PageStatus['TotRows'] / $PageStatus['PerPage']);
																		// 總共要抓幾頁

			echo date('Y/m/d H:i:s')." 共 {$TotalPage} 頁，共 {$TotalWoods} 筆，限制 {$Finder->limit_woods} 筆。<br />";
		}

		if(!ParseWoodsList($Finder, $WoodsList)) break;					// 分析資料並儲存
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品類別區
	 * --------------------------------------------------------------------------------------------------------------------
	 */
	
	echo date('Y/m/d H:i:s')." 抓取商品類別... <br />";

	if(count($Finder->tmp_category['Woods']) > 0) foreach($Finder->tmp_category['Woods'] as $LoopWoods) {
		$PageCode = GetWoodsPageCode($Finder, $LoopWoods);

		$ParseCategoryArray = array();
		preg_match(
			'/dimension3\',\'([\s\S]*?)\'[\s\S]*?dimension4\',\'([\s\S]*?)\'[\s\S]*?dimension5\',\'([\s\S]*?)\'[\s\S]*?dimension6\',\'([\s\S]*?)\'/', 
			$PageCode, 
			$ParseCategoryArray
		);

		if(count($ParseCategoryArray) > 1) for($LoopCategory = 1; $LoopCategory < count($ParseCategoryArray); $LoopCategory ++) {
			$Finder->create_relation($ParseCategoryArray[$LoopCategory], md5($LoopWoods));
			$Finder->create_category($ParseCategoryArray[$LoopCategory], $ParseCategoryArray[$LoopCategory]);
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
		'{$page}' => $Page
	));

	$str_woods_code = $aFinder->get_html_code($woods_url);

	return $str_woods_code;
}

function ParseWoodsPage($WoodsCode) {
	$ParseWoodsPageArray = array();
	preg_match(
		'/pagenum:([0-9]+);nr:([0-9]+);n_sr:([0-9]+);/', 
		$WoodsCode, 
		$ParseWoodsPageArray
	);

	if(count($ParseWoodsPageArray) != 4) return Array(
		"NowPage" => 0,
		"TotRows" => 0,
		"PerPage" => 0,
	);

	return Array(
		"NowPage" => $ParseWoodsPageArray[1],
		"TotRows" => $ParseWoodsPageArray[2],
		"PerPage" => $ParseWoodsPageArray[3]
	);
}

function ParseWoodsList($aFinder, $WoodsCode) {
	$ParseWoodsArray = array();
	preg_match_all(
		'/<div class=\"item yui3-.*\">[\s\S]*?<a href=\".*gdid=(\d+)\" title=\"(.*)\">[\s\S]*?<img[\s\S]*?src=\"([\s\S]*?)\">[\s\S]*?<span class=\"srp-promo.*\">[\s\S]*?<em>(.*)<\/em>/', 
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
			$ParseWoodsArray[3][$k]
		);
		$aFinder->tmp_category['Woods'][] = $ParseWoodsArray[1][$k];
	}
	return true;
}

function GetWoodsPageCode($aFinder, $WoodsID) {
	$woods_url = strtr($aFinder->current_type['ftdetial_url'], Array(
		'{$data}' => $WoodsID
	));

	return $aFinder->get_html_code($woods_url);
}