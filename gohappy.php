<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('gohappy');											// 建立 Finder

$Finder->set_start_time();

if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$loop_page = 1;

	get_item($Finder, $loop_page, $loop_value);

	//for($i = 0; )

	if(count($Finder->tmp_category) == 0) {
		$Finder->finder_error("資料抓取錯誤", 'tmp_category', 700 );
	} else foreach($Finder->tmp_category as &$v) {
		//var_dump($v); die();
		get_item_by_category($Finder, $loop_page, $loop_value, $v->category_id, $v->category_lv, $v->category_name, $v->category_cid);
		echo count($Finder->tmp_category).'<br />';
	}

	// if(count($Finder->repeat_category) > 0) foreach($Finder->repeat_category as $v) {
																		// 取得 商品 開始
		// $woods_url = strtr($Finder->current_type['ftdetial_url'], Array(
		// 	'{$data}' => $v,
		// ));

		// $str_woods_code = $Finder->get_html_code($woods_url);

		// $main_category_name = 0;
		// preg_match("/storePath nonedrop\\\"><span>(.*)<\\/span><\\/li>/u", $str_woods_code, $main_category_name);
		// //$main_category_name = $main_category_name[1];
		
		// if(count($main_category_name) != 2) {
		// 	var_dump($main_category_name); die();		
		// }


		// $mall_id = 0;
		// preg_match_all("/productMallId\\\" value=\"([0-9]+)\"/u", $str_woods_code, $mall_id);
		// $mall_id = parse_search_item_num($mall_id);

		// $store_id = 0;
		// preg_match_all("/productStoreId\\\" value=\\\"([0-9]+)\\\"/u", $str_woods_code, $store_id);
		// $store_id = parse_search_item_num($store_id);
		
		// $category_id = 0;
		// preg_match_all("/productCategoryId\\\" value=\\\"([0-9]+)\\\"/u", $str_woods_code, $category_id);
		// $category_id = parse_search_item_num($category_id);

		// $price_id = 0;
		// preg_match_all("/<span class=\\\"pricing\\\">\\$([0-9\\,]+)<\/span>/u", $str_woods_code, $price_id); //  建議售價
		// $price_id = parse_search_item_num($price_id);

		// $offer_id = 0;
		// preg_match_all("/<span class=\\\"price_txt\\\".*>([0-9]+)<\/span>/u", $str_woods_code, $offer_id);	//  特惠價
		// $offer_id = parse_search_item_num($offer_id);
		
		// $store_html_code = $Finder->get_html_code("http://www.gohappy.com.tw/{$mall_id}/{$store_id}/3/{$category_id}.json");

		// $store_html_replace_code = str_replace('var data = ', '', $store_html_code);

		// $sotre_decode = json_decode($store_html_replace_code, true);

		// $get_path = array();

		// if(!empty($sotre_decode['pageModel']['categoryBlock']['categoryTreeMap'][$mall_id]['showCategoryNodeHolder'][$category_id]['categoryPath'])) {
		// 	$get_path = $sotre_decode['pageModel']['categoryBlock']['categoryTreeMap'][$mall_id]['showCategoryNodeHolder'][$category_id]['categoryPath'];
		// }

		// if(count($get_path) > 0) foreach($get_path as $category_ray) {
		// 	if(count($category_ray) > 0) foreach($category_ray as $key => $category) {
		// 		$Finder->create_relation($key, md5($v));
		// 		$Finder->create_category($key, $category);
		// 	}
		// }
		
	// }



	/*$main_category = get_main_category($Finder, $loop_value);
	if(count($main_category) > 0) foreach ($main_category as $value) {
		get_woods($Finder, $loop_value, $loop_page, $value->category_num, $value->category_id, 0);
	}*/

}
$Finder->set_stop_time();
$Finder->show_time();

/*
function get_woods ($aFinder, $loop_value, $loop_page, $loop_page_size = 20, $loop_category, $lv) {
	if(!in_array($loop_category, $aFinder->repeat_category)) {
		array_push($aFinder->repeat_category, $loop_category);			
	} else {
		return;
	}
	if(intval($loop_page_size) > 20) {
		$loop_page = ceil(intval($loop_page_size) / 20);
	} else if (intval($loop_page_size) <= 0) {
		return;
	}

	echo 'category ['.$loop_category.'] lv ['.$lv.'] <br />';

	for($i = 1; $i <= $loop_page; $i++) {
		echo '> '.$i.' <br />';
		
		$woods_html_code = $aFinder->get_html_code($aFinder->current_type['ftgetwoods_url'], true, Array(
			'searchs'	=> urlencode($loop_value),
			'pageno'	=> $i,
			'pagesize'	=> 20,
			'cid'		=> $loop_category,
			'cateLvs'	=> $lv,
		));

		$woods_html_decode = json_decode($woods_html_code);

		if($woods_html_decode->message != 'OK') continue;
		if(empty($woods_html_decode->data)) continue;
		if($woods_html_decode->data->Total <= 0) continue;

		$woods_retrun_parse = array();
		preg_match_all("/<img src=\".*product\\/(.*)\\??[\\s\\S]{1,700}<a href=\\\".*pid=([0-9]*)\\\" title=\\\"([^\\\"]*)\\\"[\\s\\S]{1,900}<strong>([0-9]+)<\\/strong>/u", $woods_html_decode->data->form, $woods_retrun_parse);


		//if(empty($woods_retrun_parse[0]) || empty($woods_retrun_parse[0]) || count($woods_retrun_parse[0]) < 20) { echo $woods_html_decode->data->form; die(); };


		save_woods($aFinder, $woods_retrun_parse, $loop_category);

		$category_return_parse = array();

		preg_match_all("/cid=\\\"([0-9]+)\\\">(.*)<\\/a>[\\s\\S]{1,30}count\\\">\\(([0-9]+)\\)<\\//u", $woods_html_decode->data->menuTree, $category_return_parse);

		if(empty($category_return_parse)) continue;

		save_category($aFinder, $loop_value, $category_return_parse, ($lv+1));
	}
}

function save_category($aFinder, $loop_value, $ray, $lv) {
	$return_ray = array();
	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return ;
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {
		echo '>> '.$ray[2][$k].' ['.$ray[1][$k].'] <br />';
		$aFinder->create_category($ray[1][$k], $ray[2][$k]);		
		get_woods($aFinder, $loop_value, 1, $ray[3][$k], $ray[1][$k], $lv);
	}

}

function save_woods($aFinder, $ray, $category) {
	echo '>>> save ['.count($ray).'] <br />';
	$return_ray = array();
	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return ;
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {
		$aFinder->create_woods($ray[2][$k], $ray[3][$k], 0, $ray[4][$k], $category, $ray[1][$k]);
	}
}
//*/

function get_item_by_category($aFinder, $loop_page, $loop_value, $cate, $lv, $name, $cate_cid, $per_page = 20) {
	echo 'start get_item <br />';
	$search_main_category = '';
	for($i = 1; $i <= $loop_page; $i ++) {
		//http://m.gohappy.com.tw/smartphone/SearchAdv.do?searchs={$data}&sids={$cate}&pageindex={$page}&cateLvs={$lv}&cid={$cate}
		echo '> '.$i.' / '.$loop_page.' ['.$name.'] <br />';
		$main_category_url = strtr($aFinder->current_type['ftgetmaincategory_url'], Array(
			'{$data}' => urlencode($loop_value),
			'{$page}' => $i,
			'{$pageno}' => $per_page,
			'{$cate}' => $cate,
			'{$cate_cid}' => $cate_cid,
			'{$lv}' => $lv,
		));

		$main_category_html_code = $aFinder->get_html_code($main_category_url);

		if($i == 1) {
			$search_item_num_return_parse = array();
			preg_match_all("/<b>搜尋結果\\:<span>([0-9]+)<\\/span>項<\\/b>/", $main_category_html_code, $search_item_num_return_parse);
			//var_dump($search_item_num_return_parse); die();
			$search_item_num = parse_search_item_num($search_item_num_return_parse);


			if($search_item_num <= 0) $aFinder->finder_error("資料抓取錯誤", 'search_item_num '.$search_item_num, 800 );

			$loop_page = ceil(intval($search_item_num) / $per_page);

			$search_main_category_ray_parse = array();
			preg_match("/<option value=\\\"([0-9]+)\\,(.*)\\\">全部<\\/option>/", $main_category_html_code, $search_main_category_ray_parse);
			//var_dump($search_main_category_ray_parse); die();
			if(!isset($search_main_category_ray_parse[2])) {
				var_dump($search_main_category_ray_parse); die();
			}
			$search_main_category = $search_main_category_ray_parse[2];
		}

		$search_item_return_parse = array();
		//preg_match("/<a href=\\\".*pid=([0-9]+)\\\">[\\s\\S]*?<img src=\\\"([^\\\"]+)\\\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]*?<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $searchText)
		//preg_match("/<a href=\".*pid=([0-9]+)\\\">[\\s\\S]{1,200}<img src=\\\".*product\\/([^\\\"]+)\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]{1,700}<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $searchText)
		preg_match_all("/<a href=\\\".*pid=([0-9]+)\\\">[\\s\\S]*?<img src=\\\"([^\\\"]+)\\\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]*?<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $main_category_html_code, $search_item_return_parse);
		//var_dump($search_item_return_parse); die();

		$search_category_ray_parse = array();
		preg_match_all("/<option value=\\\"(.*),(.*)\\\".*>(.*)\\(([0-9]+)\\)<\\/option>/u", $main_category_html_code, $search_category_ray_parse);
		//var_dump($search_category_ray_parse); die();
		parse_category($aFinder, $search_category_ray_parse, $search_main_category);
		//$search_item = parse_search_item_num($search_item_return_parse);

		//var_dump($search_item_return_parse); die();
		if($aFinder->current_limit_woods >= $aFinder->limit_woods && $aFinder->limit_woods != -1) return true;

		$bool_item_parse = parse_search_item($aFinder, $search_item_return_parse, $cate, $name);
		if(!$bool_item_parse) $aFinder->finder_error("資料抓取錯誤", 'bool_item_parse', 801 );
	}
	echo 'end get_item <br />';

	// $main_category_return_parse = array();
	// preg_match_all("/<option.*value=\\\"[0-9]+\\,?S?([0-9]+)\\\"[\\s\\S]{1,10}>(.*)\\(([0-9]+)\\)<\\//u", $main_category_html_code, $main_category_return_parse);

	//return preg_array_sort_main_category($aFinder, $main_category_return_parse);
}

function get_item($aFinder, $loop_page, $loop_value, $per_page = 20) {
	echo 'start get_item <br />';
	for($i = 1; $i <= $loop_page; $i ++) {
		echo '> '.$i.' / '.$loop_page.' <br />';
		$main_category_url = strtr($aFinder->current_type['ftgetcategory_url'], Array(
			'{$data}' => urlencode($loop_value),
			'{$page}' => $i,
			'{$pageno}' => $per_page,
		));

		$main_category_html_code = $aFinder->get_html_code($main_category_url);

		if($i == 1) {
			$search_item_num_return_parse = array();
			preg_match_all("/<b>.{4}\\:<span>([0-9]+)<\\/span>.?<\\/b>/u", $main_category_html_code, $search_item_num_return_parse);
			$search_item_num = parse_search_item_num($search_item_num_return_parse);

			if($search_item_num <= 0) $aFinder->finder_error("資料抓取錯誤", 'search_item_num '.$search_item_num, 800 );

			$loop_page = ceil(intval($search_item_num) / $per_page);
		}

		$search_item_return_parse = array();
		//preg_match("/<a href=\\\".*pid=([0-9]+)\\\">[\\s\\S]*?<img src=\\\"([^\\\"]+)\\\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]*?<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $searchText)
		//preg_match("/<a href=\".*pid=([0-9]+)\\\">[\\s\\S]{1,200}<img src=\\\".*product\\/([^\\\"]+)\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]{1,700}<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $searchText)
		preg_match_all("/<a href=\\\".*pid=([0-9]+)\\\">[\\s\\S]*?<img src=\\\"([^\\\"]+)\\\\??.*alt=\\\"([^\\\"]+)\\\"[\\s\\S]*?<span class=\\\"price1\\\">([0-9]+)<\\/span>/u", $main_category_html_code, $search_item_return_parse);


		$search_category_ray_parse = array();
		preg_match_all("/<option value=\\\"(.*),(.*)\\\".*>(.*)\\(([0-9]+)\\)<\\/option>/u", $main_category_html_code, $search_category_ray_parse);

		parse_category($aFinder, $search_category_ray_parse);
		//$search_item = parse_search_item_num($search_item_return_parse);

		//var_dump($search_item_return_parse); die();
		if($aFinder->current_limit_woods >= $aFinder->limit_woods && $aFinder->limit_woods != -1) return true;

		$bool_item_parse = parse_search_item($aFinder, $search_item_return_parse);
		if(!$bool_item_parse) $aFinder->finder_error("資料抓取錯誤", 'bool_item_parse', 801 );
	}
	echo 'end get_item <br />';

	// $main_category_return_parse = array();
	// preg_match_all("/<option.*value=\\\"[0-9]+\\,?S?([0-9]+)\\\"[\\s\\S]{1,10}>(.*)\\(([0-9]+)\\)<\\//u", $main_category_html_code, $main_category_return_parse);

	//return preg_array_sort_main_category($aFinder, $main_category_return_parse);
}

function parse_search_item($aFinder, $ray, $cate_id = null, $cate_name = null) {
	if(count($ray) != 5) {
		echo '>>> count($ray) != 5 <br />';
		return false;
	}

	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return false;
	}

	echo '>>>> '.count($ray[0]).'<br />';

	if(count($ray[0]) == 0) {
		var_dump($ray);
		var_dump($aFinder->tmp_category);
		die();
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {

		if(is_null($cate_id)) {
			$aFinder->create_woods($ray[1][$k], $ray[3][$k], 0, $ray[4][$k], '', $ray[2][$k]);
		} else {
			$aFinder->create_woods($ray[1][$k], $ray[3][$k], 0, $ray[4][$k], $cate_id, $ray[2][$k]);
			$aFinder->create_category($cate_id, $cate_name);
		}

		echo '>> '.$ray[1][$k].' | '.$ray[3][$k].' <br />';

		if(!in_array($ray[1][$k], $aFinder->repeat_category)) {
			array_push($aFinder->repeat_category, $ray[1][$k]);			
		}
	}
	return true;
}

function parse_category($aFinder, $ray, $cid = '') {

	$return_ray = array();
	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return ;
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {
		$aFinder->tmp_category[$ray[2][$k]] = (object)array(
			'category_id' => $ray[2][$k], 
			'category_lv' => intval($ray[1][$k]) - 2, 
			'category_name' => $ray[3][$k], 
			'category_num' => $ray[4][$k],
			'category_cid' => ((intval($ray[1][$k]) - 2) != 0) ? $cid : $ray[2][$k],
		);
	}
}

function parse_search_item_num($ray) {
	if(count($ray) != 2) {
		return 0;
	}

	if(!isset($ray[1][0]) || count($ray[1]) != 1) {
		return 0;
	}

	if( (!is_int($ray[1][0]) ? (ctype_digit($ray[1][0])) : true) === false ) {
		return 0;
	}

	return intval($ray[1][0]);
}

function preg_array_sort_main_category($aFinder, $ray) {
	$return_ray = array();
	for($i = 0; $i < count($ray); $i++) {
		if(!isset($ray[$i+1])) break;
		if(count($ray[$i]) != count($ray[$i+1])) return ;
	}

	if(count($ray[0]) > 0) foreach($ray[0] as $k => $v) {
		echo '>>>> '.$ray[2][$k].' ['.$ray[1][$k].'] <br />';
		$return_ray[$k] = (object)Array(
			'category_id' => $ray[1][$k],
			'category_name' => $ray[2][$k],
			'category_num' => $ray[3][$k],
			'category_lev' => 0,
		);
		$aFinder->create_category($ray[1][$k], $ray[2][$k]);
	}

	return $return_ray;
}
//*/
