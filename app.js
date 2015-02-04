var app = angular.module('surveillance', [
    'ngResource',
    'ngSanitize',
    'ui.bootstrap',
    'ui.bootstrap.pagination'
]);

app.controller('MainCtrl', function($scope, $resource) {
    $scope.currentPage = 1;
    $scope.itemsPerPage = 8;

    var Cameras = $resource('rest/camera/:camera');
    var Dates = $resource('rest/camera/:camera/date');
    var Items = $resource('rest/camera/:camera/date/:date');
    var Images = $resource('rest/camera/:camera/date/:date/images');
    var Videos = $resource('rest/camera/:camera/date/:date/videos');

    Cameras.get().$promise.then(function(success) {
        $scope.cameras = success.cameras;
    });

    var fetchDates = function() {
        if ($scope.selectedCamera) {
            Dates.get({
                "camera": $scope.selectedCamera
            }).$promise.then(function(success) {
                $scope.dates = success.dates;
            });
        } else {
            $scope.dates = null;
        }
        if ($scope.selected) {
            fetchdata($scope.selected, $scope.page);
        } else {
            $scope.pager = null;
        }
    };

    var pageChanged = function() {
        if ($scope.allItems) {
            var begin = (($scope.currentPage - 1) * $scope.itemsPerPage),
                end = begin + $scope.itemsPerPage;
            $scope.items = $scope.allItems.slice(begin, end);
        }
    };

    var fetchItems = function() {
        $scope.allItems = null;
        $scope.items = null;
        if ($scope.selectedCamera && $scope.selectedDate) {
            Items.get({
                "camera": $scope.selectedCamera,
                "date": $scope.selectedDate
            }).$promise.then(function(success) {
                $scope.allItems = success.items;
                $scope.totalItems = $scope.allItems.length;
                $scope.currentPage = 1;
                pageChanged();
            });
        }
    };

    $scope.$watch('selectedCamera', function(newvalue, oldvalue) {
        fetchDates();
        fetchItems();
    });

    $scope.$watch('selectedDate', function(newvalue, oldvalue) {
        fetchItems();
    });

    $scope.$watch('currentPage', function(newvalue, oldvalue) {
        if (newvalue) {
            pageChanged();
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