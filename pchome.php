<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('pchome');											// 建立 Finder

if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$loop_page = 1;

	for($i = 1; $i <= $loop_page; $i++) {
																		// 取得 商品 開始
		$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
			'{$data}' => $loop_value,
			'{$page}' => $i
		));

		$str_woods_code = $Finder->get_html_code($woods_url);

		$decode_woods_code = json_decode($str_woods_code);

		if(is_null($decode_woods_code)) continue;

		if($decode_woods_code->totalRows == 0) continue;

		$int_woods_total = $decode_woods_code->totalRows;				// 總共有幾個可以抓
		$int_woods_total_page = $decode_woods_code->totalPage;			// 總共要抓幾頁
		$loop_page = $int_woods_total_page;

		$response_category = parse_woods_json($Finder, $decode_woods_code->prods);
																		// 取得 商品 結束

																		// 取得 商品 類別 開始
		$category_url = strtr($Finder->current_type['ftgetcategory_url'], Array(
			'{$data}' => implode(",", $response_category)
		));

		$str_category_code = $Finder->get_html_code($category_url);

		$matches = Array();
		$decode_matches = Array();

		preg_match("/\[(.*)\]/", $str_category_code, $matches);			// 取得商品 類別

		if(isset($matches['0'])) {
			$decode_matches = json_decode($matches['0']);
		}
		if(is_null($decode_matches)) continue;

		parse_category_json($Finder, $decode_matches);
																		// 取得 商品 類別 結束

		sleep($Finder->current_type['ftsleep_time']);
	}
}

function parse_woods_json ($aFinder, $data_array) {						// 分析 json 並儲存
	$category_ray = Array();
	if(count($data_array) > 0) foreach($data_array as $loop_data) {
		//var_dump($aFinder->current_type); die();
		$aFinder->create_woods($loop_data->Id, $loop_data->name, 0, $loop_data->price, $loop_data->cateId);		
		$category_ray[] = $loop_data->cateId;
	}
	return $category_ray;
}

function parse_category_json ($aFinder, $data_array) {					// 分析 json 並儲存
	if(count($data_array) > 0) foreach($data_array as $loop_data) {
		$aFinder->create_category($loop_data->Id, $loop_data->Name);		
	}
}
