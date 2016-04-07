/**
 * Copyright © 2016 Magento. All rights reserved.
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

            $scope.upgradeProcessError = false;

            $http.get('index.php/select-version/installedSystemPackage', {'responseType' : 'json'})
                .success(function (data) {
                    if (data.responseType == 'error') {
                        $scope.upgradeProcessError = true;
                        $scope.upgradeProcessErrorMessage = $sce.trustAsHtml(data.error);
                    } else {
                        if (!$rootScope.authRequest || !$rootScope.isMarketplaceAuthorized) {
                            $scope.isHiddenSpinner = false;
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
                                });
                        } else {
                            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                            $scope.nextState();
                        }
                    }
                })
                .error(function (data) {
                    $scope.upgradeProcessError = true;
                });

            $scope.errors = false;

            $scope.saveAuthJson = function () {
                if ($scope.auth.$valid) {
                    $http.post('index.php/marketplace/save-auth-json', $scope.user)
                        .success(function (data) {
                            $scope.saveAuthJson.result = data;
                            if ($scope.saveAuthJson.result.success) {
                                $scope.logout = false;
                                $localStorage.isMarketplaceAuthorized = true;
                                $scope.errors = false;
                                $scope.nextState();
                            } else {
                                $localStorage.isMarketplaceAuthorized = false;
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

            $scope.validate = function() {
                if ($scope.user.$valid) {
                    $scope.user.submitted = false;
                } else {
                    $scope.user.submitted = true;
                }
            }
        }]);
