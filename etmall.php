<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('etmall');											// 建立 Finder

$Finder->set_start_time();

if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$per_page = 20;
	$loop_page = 1;

	$Finder->tmp_category = array('');

	/*array_walk($Finder->tmp_category, function (&$v, $k) {
		if($k == 0) {
			$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
				'{$data}' => urlencode($loop_value),
				'{$page}' => $loop_page,
				'{$pageno}' => $per_page,
				'{$category}' => "{$v}",
			));

			$str_woods_code = $Finder->get_html_code($woods_url);
			//paseWoods($Finder, $str_woods_code);
			$category_array = paseCategory($Finder, $str_woods_code);
			saveCategory($Finder, $category_array);
			//$loop_page = paseWoodsPage($Finder, $str_woods_code);
			var_dump($Finder->tmp_category);
		} else {
			$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
				'{$data}' => urlencode($loop_value),
				'{$page}' => $loop_page,
				'{$pageno}' => $per_page,
				'{$category}' => $v,
			));
			$str_woods_code = $Finder->get_html_code($woods_url);
			//paseWoods($Finder, $str_woods_code);
			//$loop_page = paseWoodsPage($Finder, $str_woods_code);
			var_dump($Finder->tmp_category);
		}
	});*/

	if(count($Finder->tmp_category) > 0) foreach($Finder->tmp_category as $k => &$v) {
		echo ' ---------- > '.$v;
		if($k == 0) {
			$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
				'{$data}' => urlencode($loop_value),
				'{$page}' => $loop_page,
				'{$pageno}' => $per_page,
				'{$category}' => $v,
			));

			$str_woods_code = $Finder->get_html_code($woods_url);
			//paseWoods($Finder, $str_woods_code);
			$category_array = paseCategory($Finder, $str_woods_code);
			saveCategory($Finder, $category_array);
			//$loop_page = paseWoodsPage($Finder, $str_woods_code);
			// var_dump($Finder->tmp_category);
		} else {
			$woods_url = strtr($Finder->current_type['ftgetwoods_url'], Array(
				'{$data}' => urlencode($loop_value),
				'{$page}' => $loop_page,
				'{$pageno}' => $per_page,
				'{$category}' => $v,
			));
			$str_woods_code = $Finder->get_html_code($woods_url);
			$category_array = paseCategory($Finder, $str_woods_code);
			saveCategory($Finder, $category_array);
			//paseWoods($Finder, $str_woods_code);
			//$loop_page = paseWoodsPage($Finder, $str_woods_code);
			// var_dump($Finder->tmp_category);
		}
		echo ' ---------- > end';
	}


}

$Finder->set_stop_time();
$Finder->show_time();

function paseWoods($aFinder, $html_code) {
	$woods_return_parse = array();

	preg_match_all("/<li class=\\\".*left\\\">[\\s\\S]*?Good_ID = (.*)\\;[\\s\\S]*?DoubleToSingle\\(\\'(.*)\\'\\)[\\s\\S]*?Sys_showSearchPRCValue\\(\\'(.*?)\\', \\'([0-9]+)\\'\\)/", $html_code, $woods_return_parse);
	//var_dump($woods_return_parse);
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

	//echo $woods_return_parse[1][0];
	return json_decode($woods_return_parse[1][0]);
}

function saveCategory($aFinder, $ray) {
	if(!is_object($ray)) return ;
	if($ray->Data->Count <= 0) return;
	if(count($ray->Data->Repeater->Content) != $ray->Data->Count) return ;

	if(count($ray->Data->Repeater->Content) > 0) foreach($ray->Data->Repeater->Content as $v) {
		$aFinder->tmp_category[] = $v->CategoryID;
	}
}