<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('momo');											// 建立 Finder


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

		if(is_null($WoodsList)) break;
		if($WoodsList->rtnCode != 200) break;
		if($WoodsList->rtnData->totCnt == 0) break;

		if($TotalPage == 1) {											//更新頁數
			$TotalWoods = $WoodsList->rtnData->totCnt;			// 總共有幾個可以抓
			$TotalPage = $WoodsList->rtnData->maxPage;			// 總共要抓幾頁

			echo date('Y/m/d H:i:s')." 共 {$TotalPage} 頁，共 {$TotalWoods} 筆，限制 {$Finder->limit_woods} 筆。<br />";
		}

		if(!ParseWoodsList($Finder, $WoodsList->rtnData->goodsInfoList)) break;			// 分析資料並儲存
	}
	
	/*
	 * --------------------------------------------------------------------------------------------------------------------
	 * 商品類別區
	 * --------------------------------------------------------------------------------------------------------------------
	 */
	
	echo date('Y/m/d H:i:s')." 抓取商品類別... <br />";

	if(count($Finder->tmp_category['Woods']) > 0) foreach($Finder->tmp_category['Woods'] as $LoopWoods) {
		$PageCode = GetWoodsPageCode($Finder, $LoopWoods);

		// echo '<code>'.$PageCode.'</code>'; die();

		$ParseCategoryArray = array();
		preg_match(
			'/pathArea\"[\s\S]*?<li><a[\s\S]*?>([\s\S]*?)<\/a>[\s\S]*?<li><a[\s\S]*?>([\s\S]*?)<\/a>[\s\S]*?<li>([\s\S]*?)<[\s\S]*?<li><a[\s\S]*?>([\s\S]*?)<\/a>/', 
			$PageCode, 
			$ParseCategoryArray
		);

		if(count($ParseCategoryArray) > 1) for($LoopCategory = 1; $LoopCategory < count($ParseCategoryArray); $LoopCategory ++) {
			$Finder->create_relation($ParseCategoryArray[$LoopCategory], md5($LoopWoods));
			$Finder->create_category($ParseCategoryArray[$LoopCategory], $ParseCategoryArray[$LoopCategory]);
			// echo $ParseCategoryArray[$LoopCategory]; die();
		}
	}

	/*$CategoryList = GetCategoryList($Finder, $LoopKeyWord);						
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
	}*/
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
	$str_woods_code = $aFinder->get_html_code($aFinder->current_type['ftgetwoods_url'], true, Array(
		'data' => '{"flag":"searchEngine","data":{"searchValue":"'.$Keyword.'","searchType":"1","currPage":"'.$Page.'","cp":"N","NAM":"N","first":"N","superstore":"N","normal":"N","cateCode":"","cateLevel":"-1","priceS":"最低價","priceE":"最高價"}}'
	));

	// $str_woods_code = $aFinder->get_html_code($woods_url);

	$decode_woods_code = json_decode($str_woods_code);

	return $decode_woods_code;
}

function ParseWoodsList($aFinder, $WoodsList) {
	if(count($WoodsList) > 0) foreach($WoodsList as $loop_data) {
		if($aFinder->current_limit_woods == $aFinder->limit_woods) {	// && $aFinder->limit_woods != -1 // 超過 limit
			echo date('Y/m/d H:i:s')." Out of limit.<hr />";
			return false;
		}
		$wpic_url = ParseWoodsImageUrl($loop_data->GOODS_CODE);

		$aFinder->create_woods(
			$loop_data->GOODS_CODE, 
			$loop_data->GOODS_NAME, 
			0, 
			$loop_data->SALE_PRICE, 
			'', 
			$loop_data->IMG_URL.$wpic_url);		
		$aFinder->tmp_category['Woods'][] = $loop_data->GOODS_CODE;
		// $aFinder->tmp_category['Category'][] = $loop_data->cateId;
	}
	return true;
}

function ParseWoodsImageUrl ($wuid) {										// momo 圖片網址 生成
	$input_line = sprintf('%010d', $wuid);
	$output = array();
	preg_match('/([0-9]{4})([0-9]{3})([0-9]{3})/', $input_line, $output);
	if(count($output) != 4) return '';
	$str_output = $output[1].'/'.$output[2].'/'.$output[3].'/'.$wuid.'_R.jpg';

	return $str_output;
}

function GetWoodsPageCode($aFinder, $WoodsID) {
	$woods_url = strtr($aFinder->current_type['ftdetial_url'], Array(
		'{$data}' => $WoodsID
	));

	return $aFinder->get_html_code($woods_url);
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