/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('auth-dialog', ['ngStorage', 'ngDialog'])
    .controller('authDialogController', ['$rootScope', '$scope', '$state', '$http', 'ngDialog', '$localStorage',
        function ($rootScope, $scope, $state, $http, ngDialog, $localStorage) {
        $scope.user = {
            username : $localStorage.marketplaceUsername ? $localStorage.marketplaceUsername : '',
            password : '',
            submitted : ''
        };
        $scope.errors = false;
        if (!$rootScope.authRequest) {
            $rootScope.isAuthLoadingComplete = false;
            $http.post('index.php/marketplace/check-auth', [])
                .success(function (response) {
                    if (response.success) {
                        $localStorage.marketplaceUsername = $scope.user.username = response.data.username;
                        $localStorage.isMarketplaceAuthorized = true;
                    } else {
                        $localStorage.isMarketplaceAuthorized = false;
                    }
                    $rootScope.isAuthLoadingComplete = true;
                    $rootScope.authRequest = true;
                    $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                })
                .error(function (data) {
                    $rootScope.isAuthLoadingComplete = true;
                });
        } else {
            $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
            $rootScope.isAuthLoadingComplete = true;
        }

        $scope.open = function () {
            ngDialog.open({ scope: $scope, template: 'authDialog', showClose: false, controller: 'authDialogController'});
        };

        $scope.saveAuthJson = function () {
            if ($scope.auth.$valid) {
                $rootScope.saveAuthProccessed = true;
                $http.post('index.php/marketplace/save-auth-json', $scope.user)
                    .success(function (data) {
                        $scope.saveAuthJson.result = data;
                        if ($scope.saveAuthJson.result.success) {
                            if (typeof($scope.$parent) != 'undefined') {
                                $scope.$parent.edited = false;
                                $scope.$parent.logout = false;
                            }
                            ngDialog.close();
                            $scope.errors = false;
                            $scope.logout = false;
                            $localStorage.isMarketplaceAuthorized = true;
                        } else {
                            $scope.errors = true;
                            $localStorage.isMarketplaceAuthorized = false;
                        }
                        $rootScope.isMarketplaceAuthorized = $localStorage.isMarketplaceAuthorized;
                        $rootScope.saveAuthProccessed = false;
                        $localStorage.marketplaceUsername = $scope.user.username;
                    })
                    .error(function (data) {
                        $scope.saveAuthJson.failed = data;
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
                        $localStorage.isMarketplaceAuthorized = $rootScope.isMarketplaceAuthorized = false;
                    }
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
