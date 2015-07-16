/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('readiness-check', [])
    .constant('COUNTER', 1)
    .controller('readinessCheckController', ['$rootScope', '$scope', '$http', '$timeout', 'COUNTER', function ($rootScope, $scope, $http, $timeout, COUNTER) {
        $scope.progressCounter = COUNTER;
        $scope.startProgress = function() {
            ++$scope.progressCounter;
        };
        $scope.stopProgress = function() {
            --$scope.progressCounter;
            if ($scope.progressCounter == COUNTER) {
                $scope.resetProgress();
            }
        };
        $scope.resetProgress = function() {
            $scope.progressCounter = 0;
        };
        $rootScope.checkingInProgress = function() {
            return $scope.progressCounter > 0;
        };
        $scope.requestFailedHandler = function(obj) {
            obj.processed = true;
            obj.isRequestError = true;
            $scope.hasErrors = true;
            $rootScope.hasErrors = true;
            $scope.stopProgress();
        };

        $scope.completed = false;
        $scope.hasErrors = false;

        $scope.version = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.settings = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.extensions = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.permissions = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };

        $scope.items = {
            'php-version': {
                url:'index.php/environment/php-version',
                show: function() {
                    $scope.startProgress();
                    $scope.version.visible = true;
                },
                process: function(data) {
                    $scope.version.processed = true;
                    angular.extend($scope.version, data);
                    $scope.updateOnProcessed($scope.version.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.version);
                }
            },
            'php-settings': {
                url:'index.php/environment/php-settings',
                show: function() {
                    $scope.startProgress();
                    $scope.settings.visible = true;
                },
                process: function(data) {
                    $scope.settings.processed = true;
                    angular.extend($scope.settings, data);
                    $scope.updateOnProcessed($scope.settings.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.settings);
                }
            },
            'php-extensions': {
                url:'index.php/environment/php-extensions',
                show: function() {
                    $scope.startProgress();
                    $scope.extensions.visible = true;
                },
                process: function(data) {
                    $scope.extensions.processed = true;
                    angular.extend($scope.extensions, data);
                    $scope.updateOnProcessed($scope.extensions.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.extensions);
                }
            },
            'file-permissions': {
                url:'index.php/environment/file-permissions',
                show: function() {
                    $scope.startProgress();
                    $scope.permissions.visible = true;
                },
                process: function(data) {
                    $scope.permissions.processed = true;
                    angular.extend($scope.permissions, data);
                    $scope.updateOnProcessed($scope.permissions.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.permissions);
                }
            }
        };

        $scope.isCompleted = function() {
            return $scope.version.processed
                && $scope.settings.processed
                && $scope.extensions.processed
                && $scope.permissions.processed;
        };

        $scope.updateOnProcessed = function(value) {
            if (!$rootScope.hasErrors) {
                $rootScope.hasErrors = (value != 'success');
                $scope.hasErrors = $rootScope.hasErrors;
            }
        };

        $scope.updateOnError = function(obj) {
            obj.expanded = true;
        };

        $scope.updateOnSuccess = function(obj) {
            obj.expanded = false;
        };

        $scope.updateOnExpand = function(obj) {
            obj.expanded = !obj.expanded;
        };

        $scope.hasItem = function(haystack, needle) {
            return haystack.indexOf(needle) > -1;
        };

        $scope.query = function(item) {
            return $http.get(item.url, {timeout: 3000})
                .success(function(data) { item.process(data) })
                .error(function(data, status) {
                    item.fail();
                });
        };

        $scope.progress = function() {
            $rootScope.hasErrors = false;
            $scope.hasErrors = false;
            angular.forEach($scope.items, function(item) {
                item.show();
            });
            angular.forEach($scope.items, function(item) {
                $scope.query(item);
            });
        };

        $scope.$on('$stateChangeSuccess', function (event, nextState) {
            if (nextState.id == 'root.readiness-check.progress') {
                $scope.progress();
            }
        });
    }]);
