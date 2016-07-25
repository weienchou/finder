<?php require('class_finder.php');										// 引用 Finder

$Finder = new Finder('gohappy');											// 建立 Finder

$Finder->set_start_time();
if(count($Finder->current_keyword) > 0) foreach($Finder->current_keyword as $loop_value) {
	$loop_page = 1;

	$woods_html_code = $Finder->get_html_code($Finder->current_type['ftgetwoods_url'], true, Array(
		'searchs'	=> $loop_value,
		'pageno'	=> $loop_page,
		'pagesize'	=> '20'
	));


	$retrun_parse = array();
	preg_match_all("/<img src=\\\\\\\"([^\\\\]*)\\\\\\\"[\\s\\S]{1,500}<ol class=\\\\\\\"proddata-list\\\\\\\">[\\s\\S]{1,300}<li class=\\\\\\\"prodname\\\\\\\">[\\s\\S]{1,40}<h3 title=\\\\\\\"([^\\\\\\\"]*)\\\\\\\">[\\s\\S]{1,30}<a href=\\\\\\\"([^\\\\]*)\\\\\\\"/u", $woods_html_code, $retrun_parse);


	var_dump ($retrun_parse); die();
	// $decode_main_category_code = json_decode($str_main_category_code);

}
$Finder->set_stop_time();
$Finder->show_time();
