/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('create-backup', ['ngStorage'])
    .controller('createBackupController', ['$scope', '$state', '$localStorage', function ($scope, $state, $localStorage) {
        $scope.backupInfo = {
            options: {
                code: true,
                media: true,
                db: true
            }
        };

        $scope.type = $state.current.type;

        if ($localStorage.backupInfo) {
            $scope.backupInfo = $localStorage.backupInfo;
        }

        $scope.nextButtonStatus = false;

        $scope.optionsSelected = function () {
            if (!$scope.backupInfo.options.code && !$scope.backupInfo.options.media && !$scope.backupInfo.options.db) {
                $scope.nextButtonStatus = true;
                return true;
            } else {
                $scope.nextButtonStatus = false;
                return false;
            }
        };

        $scope.goToStartUpdater = function () {
            if ($state.current.type === 'uninstall') {
                $state.go('root.data-option');
            } else {
                $state.go('root.start-updater-' + $state.current.type);
            }
        }

        $scope.$on('nextState', function () {
            $localStorage.backupInfo = $scope.backupInfo;
        });

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
