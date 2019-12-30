<?php

header("Content-Type: text/ascii");

include("config.php");

$pdo_vars = "mysql:host=" . $mysql_host . ";port=" . $mysql_port . ";dbname=" . $mysql_database . ";charset=utf8";
if (!$db = new PDO($pdo_vars, $mysql_user, $mysql_pass)) {
	die("Could not connect to DB...");
}

function create_directory($directory) {
	if (!$directory) {
	    die("Directory is not set in config...");
	}
	if (!file_exists($directory) and !mkdir($directory, 0777, true)) {
	    die("Failed to create folders...");
	}
	return true;
}

function get_filename($filename, $count, $time) {
	if (!$time) {
		$time = time();
	}
	if (!file_exists($filename)) {
		die("File not found..." . $filename);
	}
	if (!$count) {
		die("No counter...");
	}
	$extension = pathinfo($filename, PATHINFO_EXTENSION);
	return "/" . date("Ymd_His", $time) . "_" . substr($count + 1000, 1, 3) . "." . $extension;
}

function get_media_path($id, $time) {
	global $media_storage;
	if (!$time) {
		$time = time();
	}
	$directory = $media_storage;
	create_directory($directory);
	$directory = $directory . "/" . $id;
	create_directory($directory);
	$directory = $directory . "/" . date("Y", $time);
	create_directory($directory);
	$directory = $directory . "/" . date("m", $time);
	create_directory($directory);
	$directory = $directory . "/" . date("d", $time);
	create_directory($directory);
	$directory = $directory . "/" . date("H", $time);
	create_directory($directory);
	return $directory;
}

$processed = 0;
$process_max_images = isset($process_uploaded_images_limit) ? $process_uploaded_images_limit : 1000;
$process_max_time = isset($process_uploaded_images_time_limit) ? $process_uploaded_images_time_limit : 50;
$time_processing_started = time();
foreach ($camera_paths as $item) {
    $dh = opendir($item->path);
    while (false !== ($filename = readdir($dh))) {
    	if ($filename !== '.' and $filename !== '..') {
               $max_amount_images_processed = ($process_max_images > 0 and $processed === $process_max_images);
               $processing_time_exceeded = time() > ($time_processing_started + $process_max_time);
               if ($max_amount_images_processed or $processing_time_exceeded) {
			echo "[DONE] Processed " . $processed . " media items\n";
			exit;
    		}

		$source = $item->path . "/" . $filename;
    		$time = filemtime($source);
    		$size = filesize($source);
	        $datetime = date("Y-m-d H:i:s", $time);
			$media_path = get_media_path($item->camera, $time);
    		$count = 1;
    		$found = 0;
    		while ($found === 0) {
	    		$target = $media_path . get_filename($source, $count, $time);
	    		if (file_exists($target)) {
	    			$count++;
	    		} else {
	    			$found = 1;
	    		}
	    	}
	    	if (!copy($source, $target)) {
	    		die("Could not copy...");
	    	}
	    	if (!unlink($source)) {
	    		die("Could not unlink...");
	    	}
	    	$sql = "INSERT INTO items (created, camera, file, size) VALUES(NOW(), '" . $item->camera . "', '" . $target . "', '" . $size . "' )";
			if (!$db->exec($sql)) {
				die("Could not insert...");
			}
	    	//echo $source . " moved to " . $target . " (pictures.id " . $db->lastInsertId() . ")\n";
	    	$processed++;
    	}
    }
}

echo "[DONE] Processed " . $processed . " media items\n";

?>
