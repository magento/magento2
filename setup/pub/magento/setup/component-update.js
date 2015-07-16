/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-update', ['ngStorage'])
    .controller('componentUpdateController', ['$scope', '$state', '$localStorage', '$http', '$window', function ($scope, $state, $localStorage, $http, $window) {
        $scope.maintenanceCalled = false;
        $scope.maintenanceStatus = false;
        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        if ($localStorage.backupInfo) {
            $scope.backupInfoPassed = $localStorage.backupInfo;
        }
        if (!$scope.backupInfoPassed.options.code && !$scope.backupInfoPassed.options.media && !$scope.backupInfoPassed.options.db) {
            $scope.maintenanceCalled = true;
            $http.post('index.php/maintenance/index', $scope.backupInfoPassed)
                .success(function (data) {
                    if (data['responseType'] === 'success') {
                        $scope.maintenanceStatus = true;
                    } else {
                        $scope.errorMsg = data['error'];
                    }
                })
                .error(function (data) {
                    $scope.errorMsg = data;
                });

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
