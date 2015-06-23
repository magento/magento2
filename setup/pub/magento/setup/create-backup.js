/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('create-backup', ['ngStorage'])
    .controller('createBackupController', ['$scope', '$state', '$localStorage', '$http', function ($scope, $state, $localStorage, $http) {
        $scope.backupInfo = {
            options: {
                code: false,
                media: false,
                db: false,
                none: false               
            },
            backupFiles: ''            
        };

        $scope.loading = false;

        if ($localStorage.backupInfo) {
            $scope.backupInfo = $localStorage.backupInfo;
        }

        $scope.$on('nextState', function () {
            $localStorage.backupInfo = $scope.backupInfo;
        });

        $scope.takeBackup = function () {
            $scope.loading = true;
            $http.post('index.php/take-backup', $scope.backupInfo)
                .success(function (data) {
                    $scope.takeBackup.result = data;
                    if ($scope.takeBackup.result.success) {
                        $scope.backupInfo.backupFiles = $scope.takeBackup.result.backupFiles;
                        $scope.loading = false;
                        $scope.nextState();
                    }
                })
                .error(function (data) {
                    $scope.takeBackup.failed = data;
                    $scope.loading = false;
                });
        };

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.backup.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.backup.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.backup.$valid }, function(valid) {
            if (valid) {
                $scope.backup.submitted = false;
            }
        });
    }]);
