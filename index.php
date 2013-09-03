<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<title> Log Reader </title>
	<link href = "assets/css/bootstrap.css" rel="stylesheet" media="screen">
	<script src = "assets/js/jquery.js"></script>
	<script src = "assets/js/bootstrap.min.js"></script>
	<style>
		input:focus 
		{
    		outline:0px !important;
    		-webkit-appearance:none;
        }
        a:focus 
		{
    		outline:0px !important;
    		-webkit-appearance:none;
        }
        button:focus 
		{
    		outline:0px !important;
    		-webkit-appearance:none;
        }
	</style>
	<?php
		session_write_close();
		session_start();
		$dir = "temp/";
		foreach (glob($dir."*") as $file)
		{
			if (filemtime($file) < time() - 86400)
			{
   				unlink($file);
    		}
		}
		if(isset($_FILES['file']))
		{
			if(isset($_SESSION['wgetURL']))
				unset($_SESSION['wgetURL']);
			if ($_FILES["file"]["error"] > 0)
			{
				echo "<h3 class='text-error'>Error: " . $_FILES["file"]["error"] . "</h3>";
				exit();
			}
			if($_FILES['file']['type']!="text/plain")
			{	
				echo "<h2 class='text-error'> Error: Uploaded file is not a txt file. </h2>";
				exit();
			}
			$fileParts = explode(".",$_FILES['file']['name']);
			$extension = end($fileParts);
			if($extension != "txt")
			{
				echo "<h2 class='text-error'> Error: Uploaded file is not a txt file. </h2>";
				exit();	
			}
			$timestamp = time();
			echo "<input type='text' id='timestampDiv' value=".$_FILES['file']['name'].$timestamp." style='display:none;' disabled> ";
			$handle = fopen($_FILES["file"]["tmp_name"],"r");
			$handleWrite = fopen("temp/".$_FILES['file']['name'].$timestamp.".csv","w");
			$_SESSION['filename'] = $_FILES['file']['name'].$timestamp.".csv";

			$handleStat=fopen('stats.txt','a+');
			date_default_timezone_set("Asia/Calcutta");
			
		}
		if(isset($_GET['filename']))
		{
			$timestamp = time();
			$handle = fopen("temp/".$_GET['filename'],"r");
			$handleWrite = fopen("temp/".$_GET['filename'].$timestamp.".csv","w");
			$_SESSION['filename'] = $_GET['filename'].$timestamp.".csv";

			$handleStat=fopen('stats.txt','a+');
			date_default_timezone_set("Asia/Calcutta");
		}
		if(isset($_FILES['file']) || isset($_GET['filename']))
		{
			$_SESSION['time'] = array();
			$_SESSION['logLevel'] = array();
			$_SESSION['category'] = array();
			$_SESSION['code'] = array();

			while(!feof($handle))
			{
				$pos = ftell($handleWrite);
				$str = fgets($handle);
				if($str[0] < '0' || $str[0]>'9')
					continue;

				$arr = preg_split("/ +/",$str);
				if(empty($arr[3]))
					continue;
				else
				{
					if(empty($_SESSION['logLevel'][$arr[3]]))
						$_SESSION['logLevel'][$arr[3]] = array($pos);
					else
						$_SESSION['logLevel'][$arr[3]][] = $pos;
					if(empty($_SESSION['category'][$arr[4]]))
						$_SESSION['category'][$arr[4]] = array($pos);
					else
						$_SESSION['category'][$arr[4]][] = $pos;
				}
				$temp_time = substr($arr[0],2,5);
				$row = substr($arr[0],2,7).", ".$arr[3].", ".$arr[4].", ";
				$parts = explode(":",$arr[5]);
				if(empty($parts[1]))
					echo "ERR:$str";
				$parts[3] = substr($parts[3],1,strlen($parts[3])-2);
				$row .=$parts[0].", ".$parts[1].", ".$parts[2].", ".$parts[3].", ";
				$length = 0;
				for($i=6;$i<count($arr);$i++)
					$row .=$arr[$i]." ";
				$row = rtrim($row," ");
				fwrite($handleWrite,$row);
				
				if(empty($_SESSION['time'][$temp_time]))
					$_SESSION['time'][$temp_time] = array($pos);
				else
					$_SESSION['time'][$temp_time][] = $pos;
				
				if(empty($_SESSION['code'][$parts[0]]))
					$_SESSION['code'][$parts[0]] = array($pos);
				else
					$_SESSION['code'][$parts[0]][] = $pos;
			}
			fclose($handle);
			fclose($handleWrite);
			$time=date('Y-m-d h:i:s A');
			fwrite($handleStat,"$time\t\tFILEUPLOAD\t".$_SERVER['REMOTE_ADDR']."\t".$_SESSION['filename']."\t".filesize("temp/".$_SESSION['filename'])."\n");
			fclose($handleStat);
		}
		else
		{
			$handle=fopen('stats.txt','a+');
			date_default_timezone_set("Asia/Calcutta");
			$time=date('Y-m-d h:i:s A');
			fwrite($handle,"$time\t\tREFRESH:\t".$_SERVER['REMOTE_ADDR']."\n");
			fclose($handle);
		}
		
	?>
	<script>
		var pos = 0;
		<?php
			if(isset($_FILE['file']) || isset($_GET['filename']))
				echo "var start = 1;";
			else
				echo "var start = 0;";
		?>
		function validateFile()
		{
			if(!document.getElementsByName('file')[0].value)
			{
				alert('Please select a log file.');
				return false;
			}
			return true;
		}
		
		function showData()
		{
			var xmlhttp;
			if (window.XMLHttpRequest)
			{
				  xmlhttp = new XMLHttpRequest();
			}
			else
			{
				 xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
			}
			xmlhttp.onreadystatechange=function()
			{
				  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				  {
				   	var values = xmlhttp.responseText.split('</table>');
				   	document.getElementById('logDiv2').innerHTML+=values[0];
				   	pos = values[1];
				   	alert(values[1]);
				  }
			}
			xmlhttp.open('GET','readLog.php?pos='+pos+'&file='+document.getElementById('timestampDiv').value,true);
			xmlhttp.send();
		}

		function getLogs(pos, noofrecords, findIntersection)
		{
			var timeval = document.getElementById('selectTime').value;
			if(start)
			{
				var logLevelval = 'ALL';
				start = 0;
			}
			else
				var logLevelval = document.getElementById('selectLogLevel').value;
			var categoryval = document.getElementById('selectCategory').value;
			var codeval = document.getElementById('selectCode').value;
			var xmlhttp;
			if (window.XMLHttpRequest)
			{
				  xmlhttp = new XMLHttpRequest();
			}
			else
			{
				 xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
			}
			xmlhttp.onreadystatechange=function()
			{
				  if (xmlhttp.readyState==4 && xmlhttp.status==200)
				  {
				   	var values = xmlhttp.responseText.split("~");
				   	document.getElementById('logDiv2').innerHTML=values[0];
				   	document.getElementById('navDiv').innerHTML=values[1];
				  }
			}
			if(findIntersection)
				xmlhttp.open('GET','readFilteredLog.php?time='+timeval+'&logLevel='+logLevelval+'&category='+categoryval+'&code='+codeval+'&noofrecords='+noofrecords+'&pos='+pos+'&findIntersection=1',true);
			else
				xmlhttp.open('GET','readFilteredLog.php?time='+timeval+'&logLevel='+logLevelval+'&category='+categoryval+'&code='+codeval+'&noofrecords='+noofrecords+'&pos='+pos,true);
			xmlhttp.send();
		}

		function validateWget()
		{
			var ip = document.getElementById('ip').value;
			var ipregex = /^(([0-1]?[0-9]?[0-9])|([2][0-5][0-5]))[.](([0-1]?[0-9]?[0-9])|([2][0-5][0-5]))[.](([0-1]?[0-9]?[0-9])|([2][0-5][0-5]))[.](([0-1]?[0-9]?[0-9])|([2][0-5][0-5]))$/;
			if(ipregex.test(ip))
				return true;
			return false;
		}

		function searchFile()
		{
			var xmlhttp;
			var query = document.getElementById('searchQuery').value;
			if (window.XMLHttpRequest)
			{
				  xmlhttp = new XMLHttpRequest();
			}
			else
			{
				 xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
			}
			xmlhttp.onreadystatechange=function()
			{
				if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
					var values = xmlhttp.responseText.split("~");
				   	document.getElementById('logDiv2').innerHTML=values[0];
				   	document.getElementById('navDiv').innerHTML=values[1];
				}
			}
			xmlhttp.open('GET','searchFile.php?query='+query,true);
			xmlhttp.send();
		}

		if(start)
		{
			getLogs(0,1);
		}

	</script>
</head>
<body>
	<?php 
		if(!isset($_FILES['file']) && !isset($_GET['filename']))
			echo "<div class='hero-unit' align='center'>
				<h2>GST Debug Log Reader</h2>
			</div>";
		else
			echo "<br>";
	?>
	<div align = 'center'>
		<?php
			if(!isset($_FILES['file']) && !isset($_GET['filename']))
				echo "<br>";
		?>
		<div class='well' style=<?php if(isset($_FILES['file']) || isset($_GET['filename']))
										echo "\"width:80%;display:none;\""; 
									else 
										echo "\"width:80%;\""; 
									?> id='wgetDiv'>
			<form action="wgetLog.php" method= "GET" onsubmit='return validateWget()'>
				Get log from IP address<br><br>
				<input id='ip' name='ip' type='text' placeholder='Enter IP' required>
				<input id='path' name='path' type='text' placeholder='Enter Path' required>
				<div class='form-actions'>
					<input type='submit' class='btn btn-large btn-inverse span3' value='Get Log File'>
				</div>
			</form>
		</div>
		<?php 
			if(!isset($_FILES['file']) && !isset($_GET['filename']))
				echo "<h4> OR </h4>";
		?>
		<div class='well' style=<?php if(isset($_FILES['file']) || isset($_GET['filename']))
										echo "\"width:80%;display:none;\""; 
									else 
										echo "\"width:80%;\""; 
									?> id='uploadDiv'>
			<form action="index.php" method= "POST" enctype="multipart/form-data" onsubmit='return validateFile()'>
				<label for="file">Drop the log here</label>
				<input type="file" name="file" id="file" class='btn btn-large btn-success'>
				<div class='form-actions'>
					<input class='btn btn-primary btn-large span3' type="submit" name="submit" value="Submit">
				</div>
			</form>
		</div>
		<div id='navDiv'>
			<?php
				if(isset($_FILES['file']) || isset($_GET['filename']))
					echo"<div class='form-inline'><form class='form-search form-horizontal form-inline' style='float:right; margin-right:20px'>
					<div class='input-append' style='float:right;margin-right:20px'>
						<input type='text' id='searchQuery' placeholder='Text to Search' class='search-query span2'>
						<button type='button' class='btn btn-inverse' onclick='searchFile()'>Search</button>
					</div>
					</form>";
				if(isset($_GET['filename']))
					echo '<a class="btn btn-info" style="float:right;margin-right:20px;" href="'.$_SESSION['wgetURL'].'"><i class="icon-refresh icon-white"></i>&nbsp;<b>Reupload</b></a></form>';
				if(isset($_FILES['file']) || isset($_GET['filename']))
					echo'<a class="btn btn-success" style="float:right;margin-right:20px;" href="temp/'.$_SESSION['filename'].'"><i class="icon-download-alt icon-white"></i>&nbsp;<b>Download CSV</b></a></div><br>';
			?>
		</div><br>
		<?php
			if(isset($_FILES['file']) || isset($_GET['filename']))
			{	
				echo "<div class='well form-inline'>
					<select id='selectTime' >";
				echo "<option value='none'>Filter Time</option>";
				foreach ($_SESSION['time'] as $key => $value)
					echo "<option value='$key'>".$key.".0 - ".$key.".9</option>";
				echo "</select>
					<select id='selectLogLevel'>";
				echo "<option value='none'>Filter Log Level</option>";
					echo "<option>ALL</option>";
				foreach ($_SESSION['logLevel'] as $key => $value)
					echo "<option>$key</option>";
				// echo "</select>";
				echo "</select>
					<select id='selectCategory'>";
				echo "<option value='none'>Filter Category</option>";
				foreach ($_SESSION['category'] as $key => $value)
					echo "<option>$key</option>";
				echo "</select>
					<select id='selectCode'>";
				echo "<option value='none'>Filter Code</option>";
				foreach ($_SESSION['code'] as $key => $value)
					echo "<option>$key</option>";
				echo "</select>&nbsp;&nbsp;<input type='button' class='btn btn-primary' value='Apply Filters' onclick='getLogs(0,1,1)' ></div>";
				

				echo "<div id='logDiv' style='margin:20px;'>
				<table id='logTable' class='table table-bordered table-striped table-condensed table-hover'><tr><th>Time</th><th>Level</th><th>Category</th><th>Code</th><th>Line</th><th>Function</th><th>Object</th><th style='width:50%'>Message</th></tr>
				<tbody id='logDiv2'>
				</tbody>
				</table>
			</div>";
			}
		?>
	</div>
</body>
</html>