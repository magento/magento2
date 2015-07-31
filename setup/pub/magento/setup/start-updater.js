/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('start-updater', ['ngStorage'])
    .controller('startUpdaterController', ['$scope', '$state', '$localStorage', '$http', '$window', function ($scope, $state, $localStorage, $http, $window) {
        $scope.maintenanceCalled = false;
        $scope.maintenanceStatus = false;
        if ($state.current.type === 'cm') {
            $scope.type = 'update';
            $scope.buttonText = 'Update';
            $localStorage.successPageAction = 'updated';
        } else if ($state.current.type === 'su') {
            $scope.type = 'upgrade';
            $scope.buttonText = 'Upgrade';
            $localStorage.successPageAction = 'upgraded';
        }
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
            $http.post('index.php/start-updater/update', {'packages': $scope.packages, 'type': $state.current.type})
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
        $scope.goToCreateBackup = function() {
            if ($state.current.type === 'cm') {
                $state.go('root.create-backup-cm');
            } else if ($state.current.type === 'su') {
                $state.go('root.create-backup-su');
            }
        }
    }]);
