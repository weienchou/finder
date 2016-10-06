<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('umall');											// 建立 Finder


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
			$TotalPage = ceil($PageStatus['TotRows'] / 20);
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
			'/var WTCateID[0-9]+?[\s\S]*?\"([0-9]*?)\"[\s\S]*?;/', 
			$PageCode, 
			$ParseCategoryArray
		);

		$ParseCategoryArrayList = array();
		preg_match(
			'/var V1_StoreCateBreadCrum \=[\s\S]*?\'([\s\S]*?)\';/', 
			$PageCode, 
			$ParseCategoryArrayList
		);

		if(count($ParseCategoryArrayList) != 2) continue;

		$CategoryArrayList = json_decode($ParseCategoryArrayList[1]);
		$CategoryArray = ParseCategoryArrayList($CategoryArrayList);

		if(count($ParseCategoryArray[0]) > 0) foreach($ParseCategoryArray[0] as $k => $v) {
			if(empty($ParseCategoryArray[1][$k])) continue;
			$Finder->create_relation($ParseCategoryArray[1][$k], md5($LoopWoods));
			$Finder->create_category($ParseCategoryArray[1][$k], $CategoryArray[$ParseCategoryArray[1][$k]]);
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
	if($Page == 1) {
		$str_woods_code = $aFinder->get_html_code($aFinder->current_type['ftgetwoods_url'], true, Array(
			'query_advance' 		=> 'N',
			'query_type' 			=> '0',
			'query_Scope' 			=> 'ETMall_FullText_All',
			'SearchKeyword' 		=> $Keyword,
			'PageIndex' 			=> $Page,
			'PageSize' 				=> '20',
			'query_keyword' 		=> $Keyword
		));
	} else {
		$woods_url = strtr('http://www.u-mall.com.tw/Pages/mobile_search.aspx?ProductPage={$page}&RecordsPerPage=20', Array(
			'{$page}' => $Page
		));

		$str_woods_code = $aFinder->get_html_code($woods_url);
	}

	return $str_woods_code;
}

function ParseWoodsPage($WoodsCode) {
	$ParseWoodsPageArray = array();
	preg_match(
		'/ProductPageTotalCount[\s\S]*?\'([0-9]+)\'\;/', 
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
		'/<li class=\".*left\">[\s\S]*?sc=([0-9]+)\&[\s\S]*?MultisaleNo[\s\S]*?\'([0-9]+)\'[\s\S]*?DoubleToSingle\(\'([\s\S]*?)\'\)[\s\S]*?commafy\(\'([0-9]+)\'\)/', 
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
			$ParseWoodsArray[3][$k], 
			0, 
			$ParseWoodsArray[4][$k], 
			'', 
			CreateImageUrl($ParseWoodsArray[2][$k])
		);
		$aFinder->tmp_category['Woods'][] = $ParseWoodsArray[1][$k];
	}
	return true;
}

function CreateImageUrl($ID) {
	$ID = substr($ID, 2);
	$input_line = sprintf('%08d', $ID);
	$ReturnUrl = 'http://www.u-mall.com.tw/ProductImage/'.substr($input_line, 0, 5).'/'.$input_line.'/'.(int)$input_line.'_L.jpg';
	return $ReturnUrl;
}

function GetWoodsPageCode($aFinder, $WoodsID) {
	$woods_url = strtr($aFinder->current_type['ftdetial_url'], Array(
		'{$data}' => $WoodsID
	));

	return $aFinder->get_html_code($woods_url);
}

function ParseCategoryArrayList($JsonArray) {
	$ReturnArray = Array();
	if(count($JsonArray->Data->Content) > 0) foreach($JsonArray->Data->Content as $LoopContent) {
		if(count($LoopContent->SData) > 0) foreach($LoopContent->SData as $LoopData) {
			$ReturnArray[$LoopData->CATE_ID] = $LoopData->CATE_NAME;
		}
	}
	return $ReturnArray;
}