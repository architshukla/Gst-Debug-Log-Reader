<?php
	if(empty($_GET['query']))
		return;
	
	$colors = array("INFO" => "class='info'",
			"ERROR" => "class='error'",
			"DEBUG" => "class=''",
			"WARN" => "class='warning'",
			"LOG" => "class='success'");

	session_start();
	$searchString = preg_replace("/ +/"," ", $_GET['query']);
	$searchArray = explode(" ",$searchString);
	
	$records = 0;

	$handle = fopen("temp/".$_SESSION['filename'],"r");
	while(!feof($handle))
	{
		$string = fgets($handle);
		$found = 1;
		foreach ($searchArray as $searchString)
		{
			if(!preg_match("/$searchString/i", $string))
			{
				$found = 0;
				break;
			}
		}
		if($found)
		{	
			$records++;
			$start = strpos($string,", ") + 1;
			$end = strpos($string,", ",$start);
			$color = substr($string, $start+1, $end-$start-1);
			echo "<tr ".$colors[$color]."><td>".preg_replace("/, /", "</td><td>", $string, 7)."</td></tr>";
		}
	}
	echo "~<h5 style='float:left;margin-left:20px'>Found $records records</h5>
			<form class='form-search form-horizontal form-inline' style='float:right; margin-right:20px'>
				<div class='input-append' style='float:right;margin-right:20px'>
					<input type='text' id='searchQuery' placeholder='Text to Search' class='search-query span2' value='".$_GET['query']."'>
					<button type='button' class='btn btn-inverse' onclick='searchFile()' >Search</button>
				</div>
			</form>";
	if(isset($_SESSION['wgetURL']))
	{
		echo "<a class='btn btn-info' style='float:right;margin-right:20px;' href=\"".$_SESSION['wgetURL']."\"><i class='icon-refresh icon-white'></i>&nbsp;<b>Reupload</b></a>
			<a class='btn btn-success' style='float:right;margin-right:20px;' href='temp/".$_SESSION['filename']."'><i class='icon-download-alt icon-white'></i>&nbsp;<b>Download CSV</b></a><br>";
	}
	else
	{
		echo "<a class='btn btn-success' style='float:right;margin-right:20px;' href='temp/".$_SESSION['filename']."'><i class='icon-download-alt icon-white'></i>&nbsp;<b>Download CSV</b></a><br>";
	}
?>