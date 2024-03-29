<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('momo');											// 建立 Finder

$Finder->set_start_time();
if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$loop_page = 1;

	for($i = 1; $i <= $loop_page; $i++) {
																		// 取得 商品 開始
		$woods_url = $Finder->current_type['ftgetwoods_url'];
		$str_woods_code = $Finder->get_html_code($woods_url, true, Array(
			'data' => '{"flag":"searchEngine","data":{"searchValue":"'.encode_ucf4($loop_value).'","searchType":"1","currPage":"'.$i.'","cp":"N","NAM":"N","first":"N","superstore":"N","normal":"N","cateCode":"","cateLevel":"-1","priceS":"最低價","priceE":"最高價"}}'
		));

		echo $str_woods_code; die();

		// 沒有搜尋到 {"rtnMsg":"success!","rtnCode":200,"rtnData":{"totCnt":0}}
		// 有錯誤 {"rtnMsg":"parameter incomplete","rtnCode":501,"rtnData":{}}
		$decode_woods_code = json_decode($str_woods_code);

		if(is_null($decode_woods_code)) continue;
		if($decode_woods_code->rtnCode != 200) continue;
		if($decode_woods_code->rtnData->totCnt == 0) continue;

		$int_woods_total = $decode_woods_code->rtnData->totCnt;				// 總共有幾個可以抓 
		$int_woods_total_page = $decode_woods_code->rtnData->maxPage;			// 總共要抓幾頁
		if($loop_page == 1) {
			$loop_page = $int_woods_total_page;
		}

		$response_category = parse_woods_json($Finder, $decode_woods_code->rtnData->goodsInfoList);
																		// 取得 商品 結束

																		// 取得 商品 類別 開始
		parse_category_json($Finder, encode_ucf4($loop_value), $decode_woods_code->rtnData->categoryLt, 0);

	}
}
$Finder->set_stop_time();
$Finder->show_time();

function parse_image_url ($wuid) {										// momo 圖片網址 生成
	$input_line = sprintf('%010d', $wuid);
	$output = array();
	preg_match('/([0-9]{4})([0-9]{3})([0-9]{3})/', $input_line, $output);
	if(count($output) != 4) return '';
	$str_output = $output[1].'/'.$output[2].'/'.$output[3].'/'.$wuid.'_R.jpg';

	return $str_output;
}

function parse_woods_json ($aFinder, $data_array) {						// 分析 json 並儲存
	$category_ray = Array();
	if(count($data_array) > 0) foreach($data_array as $loop_data) {
		$wpic_url = parse_image_url($loop_data->GOODS_CODE);
		$aFinder->create_woods($loop_data->GOODS_CODE, $loop_data->GOODS_NAME, 0, $loop_data->SALE_PRICE, '', $loop_data->IMG_URL.$wpic_url);		

		$category_ray = explode('##', $loop_data->CATEGORY_CODE);

		$category_ray = array_unique($category_ray);

		if(count($category_ray) > 0) foreach($category_ray as $v) {
			$aFinder->create_relation($v, md5($loop_data->GOODS_CODE));
		}
		if($aFinder->current_limit_woods >= $aFinder->limit_woods && $aFinder->limit_woods != -1) return $category_ray;
		// $category_ray[] = $loop_data->cateId;
	}
	return $category_ray;
}

function parse_category_json ($aFinder, $woods_name, $data_array, $level) {					// 分析 json 並儲存
	//for($i = 0; $i <= $level; $i++) echo '－';
	if($level > 3) return;
	if(count($data_array) > 0) foreach($data_array as $loop_data) {

		if(!in_array($loop_data->CATEGORY_CODE, $aFinder->repeat_category)) {
			array_push($aFinder->repeat_category, $loop_data->CATEGORY_CODE);			
		} else {			
			continue;
		}
		
		$aFinder->create_category($loop_data->CATEGORY_CODE, $loop_data->CATEGORY_NAME);	
		
		//*
		$count_category_level = $level;
		$str_woods_code = $aFinder->get_html_code($aFinder->current_type['ftgetwoods_url'], true, Array(
			'data' => '{"flag":"searchEngine","data":{"searchValue":"'.$woods_name.'","searchType":"1","currPage":"1","cp":"N","NAM":"N","first":"N","superstore":"N","normal":"N","cateCode":"'.$loop_data->CATEGORY_CODE.'","cateLevel":"'.$count_category_level.'","priceS":"最低價","priceE":"最高價"}}'
		));
		$count_category_level ++;

		$decode_woods_code = json_decode($str_woods_code);
		
		

		if(is_null($decode_woods_code)) continue;

		if($decode_woods_code->rtnCode != 200) continue;
		if($decode_woods_code->rtnData->totCnt == 0) continue;
		if(count($decode_woods_code->rtnData->categoryLt) == 0) continue;
		//*/

		parse_category_json($aFinder, $woods_name, $decode_woods_code->rtnData->categoryLt, $count_category_level);
		
	}
	//if($count_category_level >= 1) return ;
}

function encode_ucf4 ($str) {
	return $str;
	$encode_string = '';
	for($i = 0; $i < mb_strlen($str, 'utf-8'); $i++) {
		$encode_string .= '&#' . base_convert(bin2hex(iconv("utf-8", "ucs-4", mb_substr($str, $i, 1, 'utf8'))), 16, 10) . ';';
	}
	return $encode_string;
}
