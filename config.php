<?php

/*
 * Database configuration:
 * =======================
 *
 * Create file named "config.database.php", and place the following variables there:
 * 
 * $mysql_host = "localhost"; // Your MySQL server host
 * $mysq_port = ""; // MySQL server port
 * $mysql_database = "camera"; // MySQL database to be used
 * $mysql_user = "root"; // User with read/write privileges to the database
 * $mysql_pass = ""; // Password for the user
 * 
 */
require_once("config.database.php");

/*
 * Cameras configuration:
 * ======================
 *
 * Create file named "config.camera.php", and place the following variables there:
 *
 * $cameras[] = (object) array( "id" => "1", "name" => "Etupuoli" );
 * $camera_paths[] = (object) array( "path" => "/path/to/camera1/videos", "camera" => "1" );
 * $camera_paths[] = (object) array( "path" => "/path/to/camera1/pictures", "camera" => "1" );
 * 
 * $cameras[] = (object) array( "id" => "2", "name" => "Takapuoli" );
 * $camera_paths[] = (object) array( "path" => "/path/to/camera2/videos", "camera" => "2" );
 * $camera_paths[] = (object) array( "path" => "/path/to/camera2/pictures", "camera" => "2" );
 * 
 */
require_once("config.camera.php");

/*
 * Host configuration:
 * ===================
 *
 * Create file named "config.site.php", and place the following variables there:
 *
 * $site_url = "http://myserver.local/camerasystem/"; // Fully qualified URL of the site
 * $site_path = "/camerasystem/"; // Relative path of the site
 * 
 */
require_once("config.site.php");

?>
