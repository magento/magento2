/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('complete-backup', ['ngStorage'])
    .controller('completeBackupController', ['$scope', '$state', '$localStorage', function ($scope, $state, $localStorage) {
        $scope.backupinfo = $localStorage.backupInfo;
        var files = '';
        for (var i = 0; i < $scope.backupinfo.backupFiles.length; i++) {
            files = files + $scope.backupinfo.backupFiles[i] + '\n';
        }
        $scope.files = files;
        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.backupStatus.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.backupStatus.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.backupStatus.$valid }, function(valid) {
            if (valid) {
                $scope.backupStatus.submitted = false;
            }
        });
    }]);
