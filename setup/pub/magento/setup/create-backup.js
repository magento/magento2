/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('create-backup', ['ngStorage'])
    .controller('createBackupController', ['$scope', '$state', '$localStorage', function ($scope, $state, $localStorage) {
        $scope.backupInfo = {
            options: {
                code: false,
                media: false,
                db: false
            }
        };

        if ($localStorage.backupInfo) {
            $scope.backupInfo = $localStorage.backupInfo;
        }

        $scope.$watch('backupInfo.options.code', function() {
            $localStorage.backupInfo = $scope.backupInfo;
        });

        $scope.$watch('backupInfo.options.media', function() {
            $localStorage.backupInfo = $scope.backupInfo;
        });
        $scope.$watch('backupInfo.options.db', function() {
            $localStorage.backupInfo = $scope.backupInfo;
        });

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
