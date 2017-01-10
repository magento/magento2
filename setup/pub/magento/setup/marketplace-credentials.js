/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('marketplace-credentials', ['ngStorage'])
    .controller('MarketplaceCredentialsController', ['$scope', '$state', '$http', '$localStorage', '$rootScope', '$sce', 'authService',
        function ($scope, $state, $http, $localStorage, $rootScope, $sce, authService) {
            $scope.showCredsForm = false;
            $scope.user = {
                username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
                password : '',
                submitted : false
            };
            $scope.actionMessage = $state.current.type == 'upgrade' ? 'upgrade' : 'upgrade or install';
            $scope.errors = false;

            $scope.checkAuth = function() {
                if (!$rootScope.isMarketplaceAuthorized) {
                    $scope.isHiddenSpinner = false;
                    authService.checkAuth({
                        success: function(response) {
                            $scope.isHiddenSpinner = true;
                            $scope.user.username = response.data.username;
                            $scope.nextState();
                        },
                        fail: function(response) {
                            $scope.isHiddenSpinner = true;
                            $scope.showCredsForm = true;
                        },
                        error: function() {
                            $scope.isHiddenSpinner = true;
                            $scope.errors = true;
                        }
                    });
                } else {
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
                    authService.saveAuthJson({
                        user: $scope.user,
                        success: function(response) {
                            $scope.isHiddenSpinner = true;
                            $scope.saveAuthJson.result = response;
                            $scope.logout = false;
                            $scope.errors = false;
                            $scope.nextState();
                        },
                        fail: function(response) {
                            $scope.isHiddenSpinner = true;
                            $scope.saveAuthJson.result = response;
                            $scope.errors = true;
                        },
                        error: function(data) {
                            $scope.isHiddenSpinner = true;
                            $scope.errors = true;
                            $scope.saveAuthJson.failed = data;
                        }
                    });
                } else {
                    $scope.validate();
                }
            };

            $scope.validate = function() {
                $scope.user.submitted = !$scope.user.$valid;
            }
        }]);
