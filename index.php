<?php

include("config.php");

function spa_index() {
?>
<!DOCTYPE html>
<html ng-app="surveillance">
    <head>
        <meta charset="utf-8" />
        <title>Valvontajärjestelmä</title>
        <script>document.write('<base href="' + document.location + '" />');</script>
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="style.css" />
        <script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
        <script type="text/javascript" src="//code.angularjs.org/1.3.8/angular.min.js"></script>
        <script type="text/javascript" src="//code.angularjs.org/1.3.8/angular-resource.min.js"></script>
        <script type="text/javascript" src="//code.angularjs.org/1.3.8/angular-sanitize.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/0.12.0/ui-bootstrap-tpls.min.js"></script>
        <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="app.js"></script>
        <script type="text/javascript">function failed(event) { console.log(event); }</script>
    </head>

    <body ng-controller="MainCtrl">
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" ng-init="menuCollapsed = true" ng-click="menuCollapsed = ! menuCollapsed">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <div class="navbar-brand">Valvontajärjestelmä</div>
                </div>
                <div class="navbar-collapse collapse" collapse="menuCollapsed" style="height: 1px;">
                    <ul class="nav navbar-nav">
                        <li class="navbar-form form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-6 control-label" for="camera">Kamera:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" ng-model="camera" ng-options="camera as camera for camera in cameras" id="camera">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </li>
                        <li class="navbar-form form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-6 control-label" for="date">Päivämäärä:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" ng-model="selected" ng-options="date.value as date.label for date in dates | orderBy:'value':true" id="date">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div ng-show="selected">
                <h1>{{ camera }}</h1>
                <pagination
                    ng-model="pager.current_page" 
                    total-items="pager.total_entries" 
                    items-per-page="pager.entries_per_page" 
                    max-size="20" 
                    direction-links="true" 
                    boundary-links="true" 
                    first-text="Alkuun" 
                    previous-text="Edellinen" 
                    next-text="Seuraava" 
                    last-text="Viimeinen" 
                    rotate="false">
                </pagination>
                <div class="row">
                    <div ng-repeat="item in items | orderBy:'time':true" class="col-xs-3">
                        <h4 class="clearfix">
                            {{ item.time | date:'dd.MM.yyyy' }} klo {{ item.time | date:'HH:mm:ss' }}
                        </h4>
                        <div ng-if="item.type === 'picture'">
                            <a ng-href="{{ item.file }}" target="_blank" class="thumbnail">
                                <img ng-src="{{ item.file }}" class="img-responsive">
                            </a>
                        </div>
                        <div ng-if="item.type === 'video'" class="video">
                            <a ng-href="{{ item.file }}" target="_blank" class="thumbnail">
                                <img src="play.png" class="img-responsive">
                            </a>
                        </div>
                    </div>
                </div>
                <pagination
                    ng-model="pager.current_page" 
                    total-items="pager.total_entries" 
                    items-per-page="pager.entries_per_page" 
                    max-size="20" 
                    direction-links="true" 
                    boundary-links="true" 
                    first-text="Alkuun" 
                    previous-text="Edellinen" 
                    next-text="Seuraava" 
                    last-text="Viimeinen" 
                    rotate="false">
                </pagination>
            </div>
        </div>
    </body>

</html>
<?php
}

function json_out($data) {
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    echo json_encode($data);
}

function reverse_time_sort($a, $b) {
    return $a->time < $b->time;
}  

if (strlen($_GET["url"]) > 0) {
    $params = explode('/', $_GET["url"]);
    if ($params[0] == "rest") {
        $output = array();
        if ($params[1] == "camera" and $params[2] and file_exists($params[2])) {
            if ($params[3] == "date" and !$params[4]) {
                $dates_check = array();
                $dates = array();
                $dir = $params[2].$video_subdirectory;
                $dh  = opendir($dir);
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($dates_check[$date] != 1) {
                        $dates_check[$date] = 1;
                        $object = new stdClass();
                        $object->label = date("d.m.Y", filemtime($dir."/".$filename));
                        $object->value = $date;
                        $dates[] = $object;
                    }
                }
                closedir($dh);
                $dir = $params[2]."/Kuvat";
                $dh  = opendir($dir);
                $files = array();
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($dates_check[$date] != 1) {
                        $dates_check[$date] = 1;
                        $object = new stdClass();
                        $object->label = date("d.m.Y", filemtime($dir."/".$filename));
                        $object->value = $date;
                        $dates[] = $object;
                    }
                }
                closedir($dh);
                $output["dates"] = $dates;
            } else if ($params[3] == "date" and $params[4] and $params[5] == "videos" and file_exists($params[2].$video_subdirectory)) {
                $dir = $params[2].$video_subdirectory;
                $dh  = opendir($dir);
                $files = array();
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($date == $params[4] and substr($filename, -3) == $video_filetype) {
                        $object = new stdClass();
                        $object->time = filemtime($dir."/".$filename) * 1000;
                        $object->file = $base_url.$dir."/".$filename;
                        $object->size = filesize($dir."/".$filename);
                        $files[] = $object;
                    }
                }
                closedir($dh);
                $output["videos"] = $files;
            } else if ($params[5] == "videos") {
                $output["error"] = "Videos not found";
            } else if ($params[3] == "date" and $params[4] and $params[5] == "pictures" and file_exists($params[2]."/Kuvat")) {
                $dir = $params[2]."/Kuvat";
                $dh  = opendir($dir);
                $files = array();
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($date == $params[4] and substr($filename, -3) == $image_filename) {
                        $object = new stdClass();
                        $object->time = filemtime($dir."/".$filename) * 1000;
                        $object->file = $base_url.$dir."/".$filename;
                        $object->size = filesize($dir."/".$filename);
                        $files[] = $object;
                    }
                }
                closedir($dh);
                $output["pictures"] = $files;
            } else if ($params[5] == "pictures") {
                $output["error"] = "Pictures not found";
            } else if ($params[3] == "date" and $params[4] and $params[5] == "page" and $params[6] > 0 and file_exists($params[2].$video_subdirectory) and file_exists($params[2]."/Kuvat")) {
                $files = array();

                $dir = $params[2].$video_subdirectory;
                $dh  = opendir($dir);
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($date == $params[4] and substr($filename, -3) == $video_filetype) {
                        $object = new stdClass();
                        $object->time = filemtime($dir."/".$filename) * 1000;
                        $object->file = $base_url.$dir."/".$filename;
                        $object->size = filesize($dir."/".$filename);
                        $object->type = "video";
                        $files[] = $object;
                    }
                }
                closedir($dh);

                $dir = $params[2]."/Kuvat";
                $dh  = opendir($dir);
                while (false !== ($filename = readdir($dh))) {
                    $date = date("Y-m-d", filemtime($dir."/".$filename));
                    if ($date == $params[4] and substr($filename, -3) == $image_filetype) {
                        $object = new stdClass();
                        $object->time = filemtime($dir."/".$filename) * 1000;
                        $object->file = $base_url.$dir."/".$filename;
                        $object->size = filesize($dir."/".$filename);
                        $object->type = "picture";
                        $files[] = $object;
                    }
                }
                closedir($dh);

                $pager = new stdClass();
                $pager->entries_per_page = 32;
                $pager->total_entries = count($files);
                $pager->current_page = $params[6] + 0;

                usort($files, 'reverse_time_sort');
                $output["items"] = array_slice($files, (($pager->current_page - 1) * $pager->entries_per_page), $pager->entries_per_page);
                $output["pager"] = $pager;
            } else {
                $output["success"] = "OK";
            }
        } else if ($params[1] == "camera") {
            $output["cameras"] = $cameras;
        } else {
            $output["error"] = "Camera not found";
        }
        json_out($output);
    } else {
        spa_index();
    }
} else {
    spa_index();
}

?>
