/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('create-backup', ['ngStorage'])
    .controller('createBackupController', ['$scope', '$state', '$localStorage', '$http', function ($scope, $state, $localStorage, $http) {
        $scope.backupOptions = {
            code: false,
            media: false,
            db: false,
            none: false
        };

        $scope.loading = false;

        if ($localStorage.backupOptions) {
            $scope.backupOptions = $localStorage.backupOptions;
        }

        $scope.$on('nextState', function () {
            $localStorage.backupOptions = $scope.backupOptions;
        });

        $scope.takeBackup = function () {
            $scope.loading = true;
            $http.post('index.php/take-backup', $scope.backupOptions)
                .success(function (data) {
                    $scope.takeBackup.result = data;
                    if ($scope.takeBackup.result.success) {
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
