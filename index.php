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
                                    <select class="form-control" ng-model="selectedCamera" ng-options="camera.id as camera.name for camera in cameras" id="camera">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </li>
                        <li class="navbar-form form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-6 control-label" for="date">Päivämäärä:</label>
                                <div class="col-sm-4">
                                    <select class="form-control" ng-model="selectedDate" ng-options="date.value as date.label for date in dates | orderBy:'value':true" id="date">
                                        <option value=""></option>
                                    </select>
                                </div>
                            </div>
                        </li>
                        <li class="navbar-form form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-9 control-label" for="items">Artikkeleita per sivu:</label>
                                <div class="col-sm-3">
                                    <select class="form-control" ng-model="itemsPerPage" id="items">
                                        <option value="8">8</option>
                                        <option value="12">12</option>
                                        <option value="16">16</option>
                                        <option value="20">20</option>
                                        <option value="24">24</option>
                                    </select>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid">
            <div ng-show="items.length == 0">
                <h1>Ei tuloksia</h1>
                <p>Valitsemallesi kameralle ja päivämäärälle ei löytynyt yhtään osumaa.</p>
            </div>
            <div ng-show="items.length > 0">
                <h1>{{ camera }}</h1>
                <pagination
                    ng-model="currentPage"
                    total-items="totalItems"
                    items-per-page="itemsPerPage"
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
                        <div ng-if="item.type === 'image'">
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
                    ng-model="currentPage"
                    total-items="totalItems"
                    items-per-page="itemsPerPage"
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

$pdo_vars = "mysql:host=" . $mysql_host . ";port=" . $mysql_port . ";dbname=" . $mysql_database . ";charset=utf8";
if (!$db = new PDO($pdo_vars, $mysql_user, $mysql_pass)) {
    die("Could not connect to DB...");
}

if (strlen($_GET["url"]) > 0) {
    $params = explode('/', $_GET["url"]);
    if ($params[0] == "rest") {
        $output = array();
        if ($params[1] == "camera" and $params[2] and file_exists($media_storage . "/" . $params[2])) {
            if ($params[3] == "date" and !$params[4]) {
                $dates = array();
                $sql = "SELECT DISTINCT SUBSTRING(created, 1, 10) AS date FROM items WHERE camera='" . $params[2] . "' ORDER BY date DESC";
                foreach($db->query($sql) as $row) {
                    $object = new stdClass();
                    $object->label = substr($row['date'], 8, 2) . "." . substr($row['date'], 5, 2) . "." . substr($row['date'], 0, 4);
                    $object->value = $row['date'];
                    $dates[] = $object;
                }
                $output["dates"] = $dates;
            } else if ($params[3] == "date" and $params[4]) {
                $files = array();
                $sql = "SELECT * FROM items WHERE camera='" . $params[2] . "' AND created >= '" . $params[4] . " 00:00:00' AND created <= '" . $params[4] . " 23:59:59' ORDER BY created DESC";
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
