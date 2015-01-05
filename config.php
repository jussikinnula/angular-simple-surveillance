<?php

date_default_timezone_set("Europe/Helsinki"); // this is needed for getting the correct daily items
error_reporting(0);

$base_url = "/"; // can be a regular URL also
$cameras = [ "Etupuoli", "Takapuoli" ];
$video_subdirectory = "/Videot"; // if zero-sized string, then all media is in same directory
$video_filetype = "mkv";
$images_subdirectory = "/Kuvat"; // if zero-sized string, then all media is in same directory
$images_filetype = "jpg";

?>