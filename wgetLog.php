<?php
	function curPageURL()
	{
 		$pageURL = 'http';
 		if (!empty($_SERVER["HTTPS"]))
 		{
 			$pageURL .= "s";
 		}
 		$pageURL .= "://";
 		if ($_SERVER["SERVER_PORT"] != "80")
 		{
  			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 		}
 		else 
 		{
  			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 		}
 		return $pageURL;
	}

	session_start();
	$_SESSION['wgetURL'] = curPageURL();
	$command ="wget ";
	$filename = "log_".time();
	$command .= " -O ".$filename." ";
	$command .= "ftp://".$_GET['ip']."/".$_GET['path'];
	echo $command;
	echo $_SESSION['wgetURL'];
	echo shell_exec("start cmd /c call scripts/wgetLog.bat \"".$command."\"");
	header("Location: index.php?filename=$filename");
?>