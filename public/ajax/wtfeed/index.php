<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
	<title>AjaxDNS Userstats</title>
	<style type="text/css">
		.col {
			width:150px;
		}
	</style>
</head>
<body>
<?php
@mysql_connect('localhost','cotton_ajax','ajaxpw');
mysql_select_db('cotton_ajax');
$results = @mysql_query("SELECT * FROM  `queries` ORDER BY  `queries`.`id` DESC") or die(mysql_error());
$row = mysql_fetch_array($results);
echo "<pre>";
print_r($row);
echo "</pre>";
$iter = 0;
foreach($row as $result){
	switch($iter){
	case 1:
		echo "<span class=\"col\">".$result."</span>\n";
		break;
	case 2:
		echo "<span class=\"col\">".$result."</span>\n";
		break;
	case 3:
		echo "<span class=\"col\">".$result."</span>\n";
		break;
	case 4:
		echo "<span class=\"col\">".$result."</span>\n";
		echo "<br/>";
		$iter = 0;
		break;
	}
	$iter++;
}
?>
</body>
