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
            $scope.actionMessage = $state.current.type == 'upgrade' ? 'upgrade' : 'upgrade or install';
            $scope.errors = false;

            $scope.checkAuth = function() {
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
                            $scope.isHiddenSpinner = true;
                            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                            $rootScope.authRequest = true;
                        })
                        .error(function() {
                            $scope.isHiddenSpinner = true;
                            $localStorage.isMarketplaceAuthorized = false;
                            $scope.errors = true;
                        });
                } else {
                    $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                    $scope.nextState();
                }
            };

            $scope.upgradeProcessError = false;
            if ($state.current.type == 'upgrade') {
                $http.get('index.php/select-version/installedSystemPackage', {'responseType' : 'json'})
                    .success(function (data) {
                        if (data.responseType == 'error') {
                            $scope.upgradeProcessError = true;
                            $scope.upgradeProcessErrorMessage = $sce.trustAsHtml(data.error);
                        } else {
                            $scope.checkAuth();
                        }
                    })
                    .error(function (data) {
                        $scope.upgradeProcessError = true;
                    });
            } else {
                $scope.checkAuth();
            }

            $scope.saveAuthJson = function () {
                if ($scope.auth.$valid) {
                    $scope.isHiddenSpinner = false;
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
                            $scope.isHiddenSpinner = true;
                            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                            $localStorage.marketplaceUsername = $scope.user.username;
                        })
                        .error(function (data) {
                            $scope.isHiddenSpinner = true;
                            $scope.saveAuthJson.failed = data;
                            $localStorage.isMarketplaceAuthorized = false;
                            $scope.errors = true;

                        });
                } else {
                    $scope.validate();
                }
            };

            $scope.validate = function() {
                $scope.user.submitted = !$scope.user.$valid;
            }
        }]);
