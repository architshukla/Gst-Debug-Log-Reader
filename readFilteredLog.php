<?php
	function fetchFilePointers($arrayOfArrays, $records)
	{
		$fetched = 0;
		$size = count($arrayOfArrays);
		$fileIndex = array();
		foreach($arrayOfArrays[0] as $index)
		{
			if($fetched < $records)
			{
				for($i=1;$i<$size;$i++)
				{
					if(!in_array($index, $arrayOfArrays[$i]))
						break;
				}
				if($i == $size)
				{	
					$fetched++;
					$fileIndex[] = $index; 
				}
			}
			else
				return $fileIndex;
		}
	}

	session_start();
	$handle = fopen("temp/".$_SESSION['filename'],"r");
	$pos = $_GET['pos'];

	$records = 20;
	if(isset($_GET['recordsToFetch']))
		$recordsToFetch +=  intval($recordsToFetch);
	else
		$recordsToFetch = 20;
	$iPos = $pos;

	$colors = array("INFO" => "class='info'",
			"ERROR" => "class='error'",
			"DEBUG" => "class=''",
			"WARN" => "class='warning'",
			"LOG" => "class='success'");

	if(isset($_GET['findIntersection']))
	{
		unset($_SESSION['finalarray']);
		$arrays = array();
	
		if($_GET['time']!='none')
			$arrays[] = $_SESSION['time'][$_GET['time']];
		if($_GET['logLevel']!='none')
		{
			if($_GET['logLevel']=='ALL')
			{
				$arrays[] = array_merge($_SESSION['logLevel']['INFO'],$_SESSION['logLevel']['ERROR'],$_SESSION['logLevel']['DEBUG'],$_SESSION['logLevel']['WARN'],$_SESSION['logLevel']['LOG']);
			}
			else
				$arrays[] = $_SESSION['logLevel'][$_GET['logLevel']];
		}
		if($_GET['category']!='none')
			$arrays[] = $_SESSION['category'][$_GET['category']];
		if($_GET['code']!='none')
			$arrays[] = $_SESSION['code'][$_GET['code']];
	
		$tempcount = count($arrays);
		if($tempcount == 0)
			$finalarray = array();
		else if($tempcount == 1)
			$finalarray = $arrays[0];
		else	
			// $finalarray = fetchFilePointers($arrays, $records);
			$finalarray = call_user_func_array("array_intersect", $arrays);
		$_SESSION['finalarray'] = $finalarray;
	}
	else
	{
		$finalarray = $_SESSION['finalarray'];
	}

	
	
	$n = count($finalarray);

	$forwardnoofrecords = ($_GET['noofrecords'] + $records)>$n?$n:($_GET['noofrecords'] + $records);
	if($n == 0)
		$curnoofrecords = 0;
	else
		$curnoofrecords = $_GET['noofrecords'];
	$backwardnoofrecords = ($_GET['noofrecords'] - $records)>=0?($_GET['noofrecords'] - $records):1;

	$indexArray = array_keys($finalarray);
	$indexSize = count($indexArray);
	$lastIndex = end($indexArray);

	for($j=0;$j<$indexSize;$j++)
	{
		if($indexArray[$j] == ($pos))
		{
			$iPos = $j;
			break;
		}
	}

	if($j == $indexSize)
		$j = 0;

	$backstring = "";

	if(($j-$records)>-1)
	{
		$backpos = $indexArray[$j-$records];
		$backstring .= "<a style='float:left;' class='btn btn-info offset0 span2' href='javascript:void(0)' onclick='getLogs($backpos,$backwardnoofrecords,0)'><i class='icon-chevron-left icon-white'></i></a>";
	}

	else
	{
		$backpos = -1;
		$backstring .= "<a style='float:left;' class='btn btn-danger offset0 span2 disabled' href='javascript:void(0)'><i class='icon-chevron-left icon-white'></i></a>";
	}

	$counter = 0;
	$string = "";
	if(isset($finalarray[$lastIndex]))
	{	for($i=$iPos;$records!=$counter&&$i<$indexSize;$i++)
		{
			// if(!isset($finalarray[$i]))
			// 	continue;
			// echo "i $i index ".$indexArray[$i]." fin ".$finalarray[$indexArray[$i]]."<br>";
			$counter++;
			rewind($handle);
			fseek($handle,$finalarray[$indexArray[$i]]);
			$temp = fgets($handle);
			$start = strpos($temp,", ") + 1;
			$end = strpos($temp,", ",$start);
			$color = substr($temp, $start+1, $end-$start-1);
			$string .= "<tr ".$colors[$color]."><td>".preg_replace("/, /", "</td><td>", $temp, 7)."</td></tr>";
		}
	}
	$string .= "~";
	// for($j=0;$j<$indexSize;$j++)
	// {
	// 	if($indexArray[$j] == ($i-1))
	// 		break;
	// }

	$string .= $backstring;

	if(isset($i) && isset($indexArray[$i]))
	{
		$frontpos = $indexArray[$i];
		$string .= "<a  class='btn btn-info span2' href='javascript:void(0)' onclick='getLogs($frontpos,$forwardnoofrecords,0)'><i class='icon-chevron-right icon-white'></i></a>";
	}
	else
	{
		$frontpos = -1;
		$string .= "<a  class='btn btn-danger span2 disabled' href='javascript:void(0)'><i class='icon-chevron-right icon-white'></i></a>";
	}
	if(($forwardnoofrecords-$curnoofrecords)==$records)
		$forwardnoofrecords--;
	
	if(isset($_SESSION['wgetURL']))
	{	
		$string .= "<h5 style='float:left;margin-left:20px'>Showing $curnoofrecords - $forwardnoofrecords of $n records</h5>
			<form class='form-search form-horizontal form-inline' style='float:right; margin-right:20px'>
				<div class='input-append' style='float:right;margin-right:20px'>
					<input type='text' id='searchQuery' placeholder='Text to Search' class='search-query span2'>
					<button type='button' class='btn btn-inverse' onclick='searchFile()'>Search</button>
				</div>
			</form>
			<a class='btn btn-info' style='float:right;margin-right:20px;' href=\"".$_SESSION['wgetURL']."\"><i class='icon-refresh icon-white'></i>&nbsp;<b>Reupload</b></a>
			<a class='btn btn-success' style='float:right;margin-right:20px;' href='temp/".$_SESSION['filename']."'><i class='icon-download-alt icon-white'></i>&nbsp;<b>Download CSV</b></a><br>";
	}
	else
		$string .= "<h5 style='float:left;margin-left:20px'>Showing $curnoofrecords - $forwardnoofrecords of $n records</h5>
		<form class='form-search form-horizontal form-inline' style='float:right; margin-right:20px'>
			<div class='input-append' style='float:right;margin-right:20px'>
				<input type='text' id='searchQuery' placeholder='Text to Search' class='search-query span2'>
				<button type='button' class='btn btn-inverse' onclick='searchFile()'>Search</button>
			</div>
		</form>
	<a class='btn btn-success' style='float:right;margin-right:20px;' href='temp/".$_SESSION['filename']."'><i class='icon-download-alt icon-white'></i>&nbsp;<b>Download CSV</b></a><br>";

	echo $string;
	fclose($handle);
?>