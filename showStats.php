<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xml:lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<title> Statisics | Test Automation </title>
	<link href = "assets/css/bootstrap.min.css" rel="stylesheet" media="screen">
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
</head>

<body>
	<div class='hero-unit' align='center'>
		<h2>Statistics</h2>
	</div>
	<div class='well' style='margin:20px' align='center'>
	<?php
		if(!file_exists("stats.txt"))
		{
			echo "<h2 class='error'>Statistics file not found.</h2>";
			exit();
		}
		$handle = fopen("stats.txt","r");

		$refresh = 0;
		$testSubmit = 0;
		$ips = array();
		$fileupload = 0;
		$wgetupload = 0;
		$fileuploadSize = 0;
		$wgetuploadSize = 0;
		

		while(!feof($handle))
		{
			$parts = explode("\t",fgets($handle));
			if(count($parts) == 1)
				continue;
			if(count($parts) == 4)
			{
				$refresh++;
				if(!in_array(rtrim($parts[3]), $ips))
					$ips[] = rtrim($parts[3]);
			}
			else
			{
				$testSubmit++;
				if(trim($parts[2]," ") == 'FILEUPLOAD')
				{
					$fileupload++;
					$fileuploadSize += intval($parts[5])/(1024*1024);
				}
				else
				{
					$wgetupload++;
					$wgetSize += intval($parts[5])/(1024*1024);
				}
				if(!in_array(rtrim($parts[3]," "), $ips))
					$ips[] = rtrim($parts[3]," ");
				
								
			}
		}
		echo "<table class='table table-striped table-hover table-condensed table-bordered' >";
		echo "
		<tr><td style='width:50%'> <h3 class='text-info'> Refreshes <td> <h4> $refresh </td></tr>
		<tr class='success'><td> <h3 class='text-info'> Unique IPs</td><td> <h4> ".count($ips)."</td></tr>
		<tr><td> <h3 class='text-info'> Total Files Uploaded </td><td> <h4> ".($fileupload + $wgetupload)."<h4>
			<h5 class='text-success'> Manual Uploads: $fileupload </h5>
			<h5 > Wget Uploads: $wgetupload </h5>
		</td></tr>
		<tr class='success' ><td> <h3 class='text-info'> Total Files Uploaded </td><td> <h4> ".($fileuploadSize + $wgetuploadSize)." MB<h4>
			<h5 class='text-success'> Manual Upload Size: $fileuploadSize MB</h5>
			<h5 > Wget Upload Size: $wgetuploadSize MB</h5>
		</td></tr>";
		
		echo "</table>";
	?>
	</div>
</body>
</html>