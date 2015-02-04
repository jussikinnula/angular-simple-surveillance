<?php

date_default_timezone_set("Europe/Helsinki"); // this is needed for getting the correct daily items
error_reporting(0);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once("config.php");

if ($_GET and $_GET["url"] and strlen($_GET["url"]) > 0) {
    $params = explode('/', $_GET["url"]);
}

$pdo_vars = "mysql:host=" . $mysql_host . ";port=" . $mysql_port . ";dbname=" . $mysql_database . ";charset=utf8";
if (!$db = new PDO($pdo_vars, $mysql_user, $mysql_pass)) {
    die("Could not connect to DB...");
}

$output = array();
if (isset($_GET["camera"]) and file_exists($media_storage . "/" . $params[2])) {
    if (isset($_GET["date"]) and strlen($_GET["date"]) === 0) {
        $dates = array();
        $sql = "SELECT DISTINCT SUBSTRING(created, 1, 10) AS date FROM items WHERE camera='" . $_GET["camera"] . "' ORDER BY date DESC";
        foreach($db->query($sql) as $row) {
            $object = new stdClass();
            $object->label = substr($row['date'], 8, 2) . "." . substr($row['date'], 5, 2) . "." . substr($row['date'], 0, 4);
            $object->value = $row['date'];
            $dates[] = $object;
        }
        $output["dates"] = $dates;
    if (isset($_GET["date"]) and strlen($_GET["date"]) > 0) {
        $files = array();
        $sql = "SELECT * FROM items WHERE camera='" . $_GET["camera"] . "' AND created >= '" . $_GET["date"] . " 00:00:00' AND created <= '" . $_GET["date"] . " 23:59:59' ORDER BY created DESC";
        foreach($db->query($sql) as $row) {
            $object = new stdClass();
            $object->time = $row['created'];
            $object->file = $site_path . $row['file'];
            $object->size = $row['size'];
            if (substr($row['file'], -3) === 'mkv') {
                $object->type = 'video';
            } else {
                $object->type = 'image';
            }
            $files[] = $object;
        }
        $output["items"] = $files;
    } else {
        $output["success"] = "OK";
    }
} else {
    $output["cameras"] = $cameras;
}

echo json_encode($output);

?>