/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('system-config', ['ngStorage'])
    .controller('systemConfigController', ['$scope', '$state', '$http','ngDialog', '$localStorage', '$rootScope',
        function ($scope, $state, $http, ngDialog, $localStorage, $rootScope) {
        $scope.user = {
            username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
            password : '',
            submitted : false
        };

        if (!$rootScope.authRequest) {
            $scope.isAuthLoadingComplete = false;
            $http.post('index.php/marketplace/check-auth', [])
                .success(function (response) {
                    if (response.success) {
                        $localStorage.marketplaceUsername = $scope.user.username = response.data.username;
                        $localStorage.isMarketplaceAuthorized = true;
                    } else {
                        $localStorage.isMarketplaceAuthorized = false;
                    }
                    $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                    $rootScope.authRequest = true;
                    $scope.isAuthLoadingComplete = true;
                })
                .error(function (data) {
                    $scope.isAuthLoadingComplete = true;
                });
        } else {
            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
            $rootScope.isAuthLoadingComplete = true;
        }

        $scope.saveAuthJson = function () {
            if ($scope.auth.$valid) {
                $scope.isAuthLoadingComplete = false;
                $http.post('index.php/marketplace/save-auth-json', $scope.user)
                    .success(function (data) {
                        $scope.saveAuthJson.result = data;
                        if ($scope.saveAuthJson.result.success) {
                            $scope.logout = false;
                            $localStorage.isMarketplaceAuthorized = true;
                            $scope.isAuthLoadingComplete = true;
                        } else {
                            $localStorage.isMarketplaceAuthorized = false;
                            $scope.isAuthLoadingComplete = true;
                        }
                        $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                        $localStorage.marketplaceUsername = $scope.user.username;
                    })
                    .error(function (data) {
                        $scope.saveAuthJson.failed = data;
                        $localStorage.isMarketplaceAuthorized = false;

                    });
            } else {
                $scope.validate();
            }
        };
        $scope.reset = function () {
            $http.post('index.php/marketplace/remove-credentials', [])
                .success(function (response) {
                    if (response.success) {
                        $scope.logout = true;
                    }
                    $localStorage.isMarketplaceAuthorized = $rootScope.isMarketplaceAuthorized = false;
                })
                .error(function (data) {
                });
        };

        $scope.validate = function() {
            if ($scope.user.$valid) {
                $scope.user.submitted = false;
            } else {
                $scope.user.submitted = true;
            }
        }
    }]);
