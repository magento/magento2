/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('configure-catalog-search', ['ngStorage', 'ngSanitize'])
    .controller('configureCatalogSearchController', ['$scope', '$localStorage' , '$state', '$http', function ($scope, $localStorage, $state, $http) {
        $scope.search = {
            config: {
                engine: null,
                elasticsearch: {},
            },
            testConnection: {
                result: {}
            },
        };

        if ($localStorage.search) {
            $scope.search.config = $localStorage.search;
        }

        $scope.$on('nextState', function () {
            $localStorage.search = $scope.search.config;
        });

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.searchConfig.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.searchConfig.submitted = true;
            }
        };

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.searchConfig.$valid }, function(valid) {
            if (valid) {
                $scope.searchConfig.submitted = false;
            }
        });

        if (!$scope.search.config.engine) {
            $http.get('index.php/configure-catalog-search/default-parameters',{'responseType' : 'json'})
                .then(function successCallback(resp) {
                    $scope.search.config = resp.data;
                });
        }

        $scope.testConnection = function(goNext) {
            $scope.checking = true;
            $scope.search.testConnection.result = {};
            $http.post('index.php/search-engine-check', $scope.search.config)
                .then(function successCallback(resp) {
                    if (resp.data.success) {
                        $scope.search.testConnection.result.success = true;
                        if (goNext) {
                            $scope.nextState();
                        } else {
                            $scope.search.testConnection.result.message = 'Test connection successful.';
                        }
                    } else {
                        $scope.search.testConnection.result.success = false;
                        $scope.search.testConnection.result.message = resp.data.error;
                    }
                    $scope.checking = false;
                }, function errorCallback() {
                    $scope.search.testConnection.result.success = false;
                    $scope.search.testConnection.result.message =
                        'An unknown error occurred. Please check configuration and try again.';
                    $scope.checking = false;
                });
        };
    }]);
