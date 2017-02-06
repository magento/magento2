/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('auth-dialog', ['ngStorage'])
    .controller('authDialogController', ['$rootScope', '$scope', '$state', '$http', '$localStorage', 'authService',
        function ($rootScope, $scope, $state, $http, $localStorage, authService) {
            $scope.user = {
                username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
                password : '',
                submitted : ''
            };
            $scope.errors = false;

            if (!$rootScope.isMarketplaceAuthorized) {
                authService.checkAuth({
                    success: function(response) {
                        $scope.user.username = response.data.username;
                    },
                    fail: function(response) {},
                    error: function() {}
                });
            }

            $scope.saveAuthJson = function () {
                if ($scope.auth.$valid) {
                    authService.saveAuthJson({
                        user: $scope.user,
                        success: function(response) {
                            $scope.saveAuthJson.result = response;
                            $scope.logout = false;
                            $scope.errors = false;
                            if (typeof($scope.$parent) != 'undefined') {
                                $scope.$parent.logout = false;
                            }
                            authService.closeAuthDialog();
                        },
                        fail: function(response) {
                            $scope.saveAuthJson.result = response;
                            $scope.errors = true;
                        },
                        error: function(data) {
                            $scope.errors = true;
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
                        authService.checkMarketplaceAuthorized();
                    }
                })
            };

            $scope.validate = function() {
                $scope.user.submitted = !$scope.user.$valid;
            }
        }]);
