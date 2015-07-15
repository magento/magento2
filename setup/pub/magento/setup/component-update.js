/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-update', ['ngStorage'])
    .controller('componentUpdateController', ['$scope', '$state', '$localStorage', '$http', '$window', function ($scope, $state, $localStorage, $http, $window) {
        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        $scope.started = false;
        $scope.errorMessage = '';
        $scope.update = function() {
            $scope.started = true;
            $http.post('index.php/component-update/update', $scope.packages)
                .success(function (data) {
                    if (data['success']) {
                        $window.location.href = '../update/index.php';
                    } else {
                        $scope.errorMessage = data['message'];
                    }
                })
                .error(function (data) {
                    $scope.errorMessage = 'Something went wrong. Please try again.';
                });
        }
    }]);
