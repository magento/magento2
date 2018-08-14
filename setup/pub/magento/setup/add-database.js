/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('add-database', ['ngStorage'])
    .controller('addDatabaseController', ['$scope', '$state', '$localStorage', '$http', function ($scope, $state, $localStorage, $http) {
        $scope.db = {
            useExistingDb: 1,
            useAccess: 1,
            host: 'localhost',
            user: 'root',
            name: 'magento'
        };

        if ($localStorage.db) {
            $scope.db = $localStorage.db;
        }

        $scope.testConnection = function () {
            $http.post('index.php/database-check', $scope.db)
                .success(function (data) {
                    $scope.testConnection.result = data;
                    if ($scope.testConnection.result.success) {
                        $scope.nextState();
                    }
                })
                .error(function (data) {
                    $scope.testConnection.failed = data;
                });
        };

        $scope.$on('nextState', function () {
            $localStorage.db = $scope.db;
        });

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.database.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.database.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.database.$valid }, function(valid) {
            if (valid) {
                $scope.database.submitted = false;
            }
        });
    }]);
