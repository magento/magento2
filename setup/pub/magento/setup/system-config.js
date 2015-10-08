/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('system-config', ['ngStorage'])
    .controller('systemConfigController', ['$scope', '$state', '$http','ngDialog', '$localStorage', '$rootScope',
        function ($scope, $state, $http, ngDialog, $localStorage, $rootScope) {
        $scope.user = {
            username : $localStorage.connectUsername ? $localStorage.connectUsername : '',
            password : '',
            submitted : false
        };

        if (!$rootScope.authRequest) {
            $scope.isAuthLoadingComplete = false;
            $http.post('index.php/connect/check-auth', [])
                .success(function (response) {
                    if (response.success) {
                        $localStorage.connectUsername = $scope.user.username = response.data.username;
                        $localStorage.isConnectAuthorized = true;
                    } else {
                        $localStorage.isConnectAuthorized = false;
                    }
                    $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
                    $rootScope.authRequest = true;
                    $scope.isAuthLoadingComplete = true;
                })
                .error(function (data) {
                    $scope.isAuthLoadingComplete = true;
                });
        } else {
            $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
            $rootScope.isAuthLoadingComplete = true;
        }

        $scope.saveAuthJson = function () {
            if ($scope.auth.$valid) {
                $scope.isAuthLoadingComplete = false;
                $http.post('index.php/connect/save-auth-json', $scope.user)
                    .success(function (data) {
                        $scope.saveAuthJson.result = data;
                        if ($scope.saveAuthJson.result.success) {
                            $scope.logout = false;
                            $localStorage.isConnectAuthorized = true;
                            $scope.isAuthLoadingComplete = true;
                        } else {
                            $localStorage.isConnectAuthorized = false;
                            $scope.isAuthLoadingComplete = true;
                        }
                        $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
                        $localStorage.connectUsername = $scope.user.username;
                    })
                    .error(function (data) {
                        $scope.saveAuthJson.failed = data;
                        $localStorage.isConnectAuthorized = false;

                    });
            } else {
                $scope.validate();
            }
        };
        $scope.reset = function () {
            $http.post('index.php/connect/remove-credentials', [])
                .success(function (response) {
                    if (response.success) {
                        $scope.logout = true;
                    }
                    $localStorage.isConnectAuthorized = $rootScope.isConnectAuthorized = false;
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
