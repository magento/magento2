/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('start-updater', ['ngStorage'])
    .controller('startUpdaterController', ['$scope', '$state', '$localStorage', '$http', '$window', function ($scope, $state, $localStorage, $http, $window) {

        $scope.type = $state.current.type;
        $scope.buttonText = $scope.type.charAt(0).toUpperCase() + $scope.type.slice(1);
        $localStorage.successPageAction = $state.current.type;

        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        if ($localStorage.dataOption) {
            $scope.dataOption = $localStorage.dataOption;
        }
        if ($localStorage.backupInfo) {
            $scope.backupInfoPassed = $localStorage.backupInfo;
        }
        if ($localStorage.titles) {
            $scope.title = $localStorage.titles[$state.current.type];
        }

        $scope.started = false;
        $scope.errorMessage = '';
        $scope.update = function() {
            $scope.started = true;
            var payLoad = {
                'packages': $scope.packages,
                'type': $state.current.type,
                'headerTitle': $scope.packages.size == 1 ? $scope.title : 'Process extensions',
                'dataOption': $localStorage.dataOption
            };
            $http.post('index.php/start-updater/update', payLoad)
                .then(function successCallback(resp) {
                    var data = resp.data;

                    if (data.success) {
                        $window.location.href = '../update/index.php';
                    } else {
                        $scope.errorMessage = data.message;
                    }
                }, function errorCallback() {
                    $scope.errorMessage = 'Something went wrong. Please try again.';
                });
        };
        $scope.goToPreviousState = function() {
            if ($state.current.type === 'uninstall') {
                $state.go('root.data-option');
            } else {
                $state.go('root.create-backup-' + $state.current.type);
            }
        };
    }]);
