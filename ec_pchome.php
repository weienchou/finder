<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('pchome');											// 建立 Finder


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

		if(is_null($WoodsList)) break;									// json 不能解析
		if($WoodsList->totalRows == 0) break;							// 沒有資料

		if($TotalPage == 1) {											//更新頁數
			$TotalWoods = $WoodsList->totalRows;						// 總共有幾個可以抓
			$TotalPage = $WoodsList->totalPage;							// 總共要抓幾頁

			echo date('Y/m/d H:i:s')." 共 {$TotalPage} 頁，共 {$TotalWoods} 筆，限制 {$Finder->limit_woods} 筆。<br />";
		}

		if(!ParseWoodsList($Finder, $WoodsList->prods)) break;			// 分析資料並儲存
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品類別區
	 * --------------------------------------------------------------------------------------------------------------------
	 */
	
	echo date('Y/m/d H:i:s')." 抓取商品類別... <br />";

	$CategoryList = GetCategoryList($Finder, $LoopKeyWord);						
	ParseCategoryList($Finder, $CategoryList);

	GetAndParseWoodsCategory($Finder, $Finder->tmp_category['Category']);
	// var_dump($Finder->tmp_category, $Finder->repeat_category);
	if(count($Finder->tmp_category['Woods']) > 0) foreach($Finder->tmp_category['Woods'] as $LoopWoods) {

		if(count($Finder->repeat_category) > 0) foreach($Finder->repeat_category as $k => $v) {
			// var_dump($k, $v, $LoopWoods); die();
			if (preg_match("/^{$k}/", $LoopWoods) === 1) {
				$Finder->create_relation($k, md5($LoopWoods));
				$Finder->create_category($k, $v);
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

function GetWoodsList($aFinder, $Keyword, $Page) {
	$woods_url = strtr($aFinder->current_type['ftgetwoods_url'], Array(
		'{$data}' => urlencode($Keyword),
		'{$page}' => $Page
	));

	$str_woods_code = $aFinder->get_html_code($woods_url);

	$decode_woods_code = json_decode($str_woods_code);

	return $decode_woods_code;
}

function ParseWoodsList($aFinder, $WoodsList) {
	if(count($WoodsList) > 0) foreach($WoodsList as $loop_data) {
		if($aFinder->current_limit_woods == $aFinder->limit_woods) {	// && $aFinder->limit_woods != -1 // 超過 limit
			echo date('Y/m/d H:i:s')." Out of limit.<hr />";
			return false;
		}

		$aFinder->create_woods(
			$loop_data->Id, 
			$loop_data->name, 
			0, 
			$loop_data->price, 
			$loop_data->cateId, 
			'http://a.ecimg.tw'.$loop_data->picB
		);
		$aFinder->tmp_category['Woods'][] = $loop_data->Id;
		$aFinder->tmp_category['Category'][] = $loop_data->cateId;
	}
	return true;
}

function GetCategoryList($aFinder, $KeyWord) {							// 取得 Pchome 大類別
	$main_category_url = strtr($aFinder->current_type['ftgetmaincategory_url'], Array(
		'{$data}' => urlencode($KeyWord),
	));

	$str_main_category_code = $aFinder->get_html_code($main_category_url);

	$decode_main_category_code = json_decode($str_main_category_code);

	return $decode_main_category_code;
}

function ParseCategoryList ($aFinder, $data_array) {					// 分析 Pchome 大類別 json
	if(empty($data_array)) return ;										// 存到 repeat_category array

	if(count($data_array) > 0) foreach($data_array as $loop_data) {
		if(!isset($loop_data->name) || empty($loop_data->name)) return;
		if(!isset($loop_data->Id) || empty($loop_data->Id)) return;

		$split_name = explode(',', $loop_data->Id);

		if(count($split_name) == 1) {
			$aFinder->repeat_category[$loop_data->Id] = $loop_data->name;
		} else if (count($split_name) == 2) foreach($split_name as $v) {
			$aFinder->repeat_category[$v] = $loop_data->name;
		}

		ParseCategoryList($aFinder, $loop_data->nodes);
	}
}

function GetAndParseWoodsCategory ($aFinder, $StringWoodsID) {			// 取得 物品 類別
	$category_url = strtr($aFinder->current_type['ftgetcategory_url'], Array(
		'{$data}' => implode(",", $StringWoodsID)
	));

	$str_category_code = $aFinder->get_html_code($category_url);

	$matches = Array();
	$decode_matches = Array();

	preg_match("/\[(.*)\]/", $str_category_code, $matches);			// 取得商品 類別

	if(isset($matches['0'])) {
		$decode_matches = json_decode($matches['0']);
	}

	if(is_null($decode_matches)) continue;

	if(count($decode_matches) > 0) foreach($decode_matches as $loop_data) {
		$aFinder->create_category($loop_data->Id, $loop_data->Name);		
	}
}