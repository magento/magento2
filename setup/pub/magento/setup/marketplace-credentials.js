/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('marketplace-credentials', ['ngStorage'])
    .controller('MarketplaceCredentialsController', ['$scope', '$state', '$http', '$localStorage', '$rootScope', '$sce',
        function ($scope, $state, $http, $localStorage, $rootScope, $sce) {
            $scope.showCredsForm = false;
            $scope.user = {
                username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
                password : '',
                submitted : false
            };

            $scope.upgradeProcessed = false;
            $scope.upgradeProcessError = false;
            $scope.isAuthLoadingComplete = false;

            $http.get('index.php/select-version/installedSystemPackage', {'responseType' : 'json'})
                .success(function (data) {
                    if (data.responseType == 'error') {
                        $scope.upgradeProcessError = true;
                        $scope.upgradeProcessErrorMessage = $sce.trustAsHtml(data.error);
                    } else {
                        if (!$rootScope.authRequest || !$rootScope.isMarketplaceAuthorized) {
                            $scope.isHiddenSpinner = false;
                            $scope.isAuthLoadingComplete = false;
                            $http.post('index.php/marketplace/check-auth', [])
                                .success(function (response) {
                                    if (response.success) {
                                        $localStorage.marketplaceUsername = $scope.user.username = response.data.username;
                                        $localStorage.isMarketplaceAuthorized = true;
                                        $scope.nextState();
                                    } else {
                                        $localStorage.isMarketplaceAuthorized = false;
                                        $scope.showCredsForm = true;
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
                            $scope.sentToNextState = true;
                            $scope.nextState();
                        }
                    }
                    $scope.upgradeProcessed = true;
                })
                .error(function (data) {
                    $scope.upgradeProcessError = true;
                });

            $scope.errors = false;
            if (!$scope.upgradeProcessError && $scope.upgradeProcessed) {
            }

            $scope.saveAuthJson = function () {
                if ($scope.auth.$valid) {
                    $http.post('index.php/marketplace/save-auth-json', $scope.user)
                        .success(function (data) {
                            $scope.saveAuthJson.result = data;
                            if ($scope.saveAuthJson.result.success) {
                                $scope.logout = false;
                                $localStorage.isMarketplaceAuthorized = true;
                                $scope.errors = false;
                                $scope.isAuthLoadingComplete = true;
                                $scope.nextState();
                            } else {
                                $localStorage.isMarketplaceAuthorized = false;
                                $scope.isAuthLoadingComplete = true;
                                $scope.errors = true;
                            }
                            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                            $localStorage.marketplaceUsername = $scope.user.username;
                        })
                        .error(function (data) {
                            $scope.saveAuthJson.failed = data;
                            $localStorage.isMarketplaceAuthorized = false;
                            $scope.errors = true;

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
