<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('umall');											// 建立 Finder

$Finder->set_start_time();

if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$per_page = 200;
	$loop_page = 0;

	$Finder->tmp_category = array('');
	echo $loop_value.'<br />';

	$loop_number = 0;
	while($loop_number - 1 <= count($Finder->tmp_category)) {

		if(!isset($Finder->tmp_category[$loop_number])) $Finder->finder_error("資料抓取錯誤", 'tmp_category', 700 );

		$LoopData = $Finder->tmp_category[$loop_number];
		if(!empty($LoopData))
			echo ' ---------- > '.$LoopData.' / '.$Finder->repeat_category[$LoopData].'<br />';
		if($loop_number == 0) {
			
			$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
				'{$data}' => urlencode($loop_value),
				'{$page}' => $loop_page,
				'{$pageno}' => $per_page,
				'{$category}' => $LoopData,
			));

			$str_woods_code = $Finder->get_html_code($woods_url);

			var_dump($str_woods_code); die();
			//paseWoods($Finder, $str_woods_code);
			$category_array = paseCategory($Finder, $str_woods_code);
			// $return_category_array = ;
			//var_dump ($str_woods_code); die();
			$return_category_array = saveCategory($Finder, $category_array);
			$return_category_array = (is_array($return_category_array)) ? $return_category_array : array();
			$Finder->tmp_category = array_merge($Finder->tmp_category, $return_category_array);

			//$loop_page = paseWoodsPage($Finder, $str_woods_code);
			// var_dump($Finder->tmp_category);
		} else {
			$loop_page_max = 0;
			for($i = 0; $i <= $loop_page_max; $i++) {
				$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
					'{$data}' => urlencode($loop_value),
					'{$page}' => $i,
					'{$pageno}' => $per_page,
					'{$category}' => $LoopData,
				));
				$str_woods_code = $Finder->get_html_code($woods_url);
				$category_array = paseCategory($Finder, $str_woods_code);
				$return_category_array = saveCategory($Finder, $category_array);
				$return_category_array = (is_array($return_category_array)) ? $return_category_array : array();
				$Finder->tmp_category = array_merge($Finder->tmp_category, $return_category_array);
				
				if($loop_page_max == 0) {
					$cate_page = $Finder->repeat_category[$LoopData.'_COUNT'];
					$loop_page_max = ceil(intval($cate_page) / $per_page) - 1;
					 //paseWoodsPage($Finder, $str_woods_code);   // 要跑幾頁
				}

				paseWoods($Finder, $str_woods_code, $LoopData);
				// var_dump($Finder->tmp_category);
				echo ' ---------- > end page ('.$i.' / '.$loop_page_max.')<br />';
			}
		}

		echo count($Finder->tmp_category).'<br />';

		$loop_number ++;

		//if($loop_number == 10) break;

	}
/*
	if(count($Finder->tmp_category) > 0) foreach($Finder->tmp_category as &$LoopData) {
		echo '>> '.count($Finder->tmp_category).' \ '. $LoopData .'<br />';
		
	}*/

	echo '>>>> '.var_dump($Finder->tmp_category).'<br />';
}

$Finder->set_stop_time();
$Finder->show_time();

function paseWoods($aFinder, $html_code, $cate_id) {
	$woods_return_parse = array();

	preg_match_all("/<li class=\\\".*left\\\">[\\s\\S]*?Good_ID = (.*)\\;[\\s\\S]*?DoubleToSingle\\(\\'(.*)\\'\\)[\\s\\S]*?Sys_showSearchPRCValue\\(\\'(.*?)\\', \\'([0-9]+)\\'\\)/", $html_code, $woods_return_parse);

	if(count($woods_return_parse[0]) > 0) foreach($woods_return_parse[0] as $k => $v) {
		//$aFinder->create_category($cate_id, $cate_name);
		//$aFinder->create_woods($woods_return_parse[1][$k], $ray[3][$k], 0, $ray[4][$k], '', $ray[2][$k]);
		$aFinder->create_woods(
			$woods_return_parse[1][$k], 
			$woods_return_parse[2][$k], 
			$woods_return_parse[4][$k], 
			$woods_return_parse[3][$k], 
			$cate_id, 
			'http://media.etmall.com.tw/ProductImage/'.$woods_return_parse[1][$k].'/'.$woods_return_parse[1][$k].'_L.jpg');
		$aFinder->create_category($cate_id, $aFinder->repeat_category[$cate_id]);
	}
}

function paseWoodsPage($aFinder, $html_code) {
	$page_num_parse = array();

	preg_match_all("/<form[\\s\\S]*?HomeSearch[\\s\\S]*?PageSize[\\s\\S]*?value=\\\"([0-9]+)\\\"/", $html_code, $page_num_parse);

	if(count($page_num_parse) != 2) {
		return 0;
	}

	if(!isset($page_num_parse[1][0]) || count($page_num_parse[1]) != 1) {
		return 0;
	}

	if( (!is_int($page_num_parse[1][0]) ? (ctype_digit($page_num_parse[1][0])) : true) === false ) {
		return 0;
	}

	return intval($page_num_parse[1][0]);
}

function paseCategory($aFinder, $html_code) {
	$woods_return_parse = array();

	preg_match_all("/var[\\s\\S]{1,4}?obj[\\s\\S]*?\\'(.*)\\'\\;/u", $html_code, $woods_return_parse);

	if(count($woods_return_parse) != 2) {
		return 0;
	}

	if(!isset($woods_return_parse[1][0]) || count($woods_return_parse[1]) != 1) {
		return 0;
	}

	return json_decode($woods_return_parse[1][0]);
}

function saveCategory($aFinder, $ray) {
	if(!is_object($ray)) return ;
	if($ray->Data->Count <= 0) return;
	if(count($ray->Data->Repeater->Content) != $ray->Data->Count) return ;

	$return_ray = array();

	if($ray->Data->Count == 1) {
		$return_ray[] = $ray->Data->Repeater->Content->CategoryID;
		$aFinder->repeat_category[$ray->Data->Repeater->Content->CategoryID] = $ray->Data->Repeater->Content->Name;
		$aFinder->repeat_category[$ray->Data->Repeater->Content->CategoryID.'_COUNT'] = $ray->Data->Repeater->Content->Count;
	} else {
		if(count($ray->Data->Repeater->Content) > 0) foreach($ray->Data->Repeater->Content as $v) {
			$return_ray[] = $v->CategoryID;
			$aFinder->repeat_category[$v->CategoryID] = $v->Name;
			$aFinder->repeat_category[$v->CategoryID.'_COUNT'] = $v->Count;
		}
	}
	return $return_ray;
}