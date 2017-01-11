/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('system-config', ['ngStorage'])
    .controller('systemConfigController', ['$scope', '$state', '$http', '$localStorage', '$rootScope', 'authService',
        function ($scope, $state, $http, $localStorage, $rootScope, authService) {
        $scope.user = {
            username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
            password : '',
            submitted : false
        };

        if (!$rootScope.isMarketplaceAuthorized) {
            $scope.isHiddenSpinner = false;
            authService.checkAuth({
                success: function(response) {
                    $scope.isHiddenSpinner = true;
                    $scope.user.username = response.data.username;
                },
                fail: function(response) {
                    $scope.isHiddenSpinner = true;
                },
                error: function() {
                    $scope.isHiddenSpinner = true;
                }
            });
        }

        $scope.saveAuthJson = function () {
            if ($scope.auth.$valid) {
                $scope.isHiddenSpinner = false;
                authService.saveAuthJson({
                    user: $scope.user,
                    success: function(response) {
                        $scope.isHiddenSpinner = true;
                        $scope.saveAuthJson.result = response;
                        $scope.logout = false;
                    },
                    fail: function(response) {
                        $scope.isHiddenSpinner = true;
                        $scope.saveAuthJson.result = response;
                    },
                    error: function(data) {
                        $scope.isHiddenSpinner = true;
                        $scope.saveAuthJson.failed = data;
                    }
                });
            } else {
                $scope.validate();
            }
        };

        $scope.reset = function () {
            authService.reset({
                success: function() {
                    $scope.logout = true;
                }
            })
        };

        $scope.validate = function() {
            $scope.user.submitted = !$scope.user.$valid;
        }
    }]);
