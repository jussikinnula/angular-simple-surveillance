var app = angular.module('surveillance', [
    'ngResource',
    'ngSanitize',
    'ui.bootstrap',
    'ui.bootstrap.pagination'
]);

app.controller('MainCtrl', function($scope, $resource) {
    $scope.page = 1;
    $scope.pager = null;

    var Cameras = $resource('rest/camera/:camera');
    var Dates = $resource('rest/camera/:camera/date');
    var Pictures = $resource('rest/camera/:camera/date/:date/pictures');
    var Videos = $resource('rest/camera/:camera/date/:date/videos');
    var Items = $resource('rest/camera/:camera/date/:date/page/:page');

    Cameras.get().$promise.then(function(success) {
        $scope.cameras = success.cameras;
    });

    var fetchdates = function(camera) {
        Dates.get({
            "camera": camera
        }).$promise.then(function(success) {
            $scope.dates = success.dates;
        });
        if ($scope.selected) {
            fetchdata($scope.selected, $scope.page);
        } else {
            $scope.pager = null;
        }
    };

    var fetchdata = function(date, page) {
        $scope.items = [];
        Items.get({
            "camera": $scope.camera,
            "date": date,
            "page": page,
        }).$promise.then(function(success) {
            $scope.items = success.items;
            $scope.pager = success.pager;
        });
    };

    $scope.$watch('camera', function(newvalue, oldvalue) {
        if (newvalue) {
            fetchdates(newvalue);
         }
    });

    $scope.$watch('selected', function(newvalue, oldvalue) {
        if (newvalue) {
            $scope.page = 1;
            fetchdata(newvalue, $scope.page);
         }
    });

    $scope.$watch('pager.current_page',function(page, old) {
        if (old && (page != old)) {
            fetchdata($scope.selected, page);
        }
    });

});

app.directive('videoPlayer', function() {
    return {
        restrict: 'E',
        templateUrl: 'videoplayer.tpl.html',
        scope: {
          'video': '='
        },
        controller: function($scope, $sce) {
            var showPlayer = function() {
                $scope.player = true;
            };
            $scope.enablePlayer = function() {
                showPlayer();
            };
        }
    };
});