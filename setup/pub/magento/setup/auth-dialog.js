/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('auth-dialog', ['ngStorage', 'ngDialog'])
    .controller('authDialogController', ['$rootScope', '$scope', '$state', '$http', 'ngDialog', '$localStorage',
        function ($rootScope, $scope, $state, $http, ngDialog, $localStorage) {
        $scope.user = {
            username : $localStorage.connectUsername ? $localStorage.connectUsername : '',
            password : '',
            submitted : ''
        };
        $scope.errors = false;
        if (!$rootScope.authRequest) {
            $rootScope.isAuthLoadingComplete = false;
            $http.post('index.php/connect/check-auth', [])
                .success(function (response) {
                    if (response.success) {
                        $localStorage.connectUsername = $scope.user.username = response.data.username;
                        $localStorage.isConnectAuthorized = true;
                    } else {
                        $localStorage.isConnectAuthorized = false;
                    }
                    $rootScope.isAuthLoadingComplete = true;
                    $rootScope.authRequest = true;
                    $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
                })
                .error(function (data) {
                    $rootScope.isAuthLoadingComplete = true;
                });
        } else {
            $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
            $rootScope.isAuthLoadingComplete = true;
        }

        $scope.open = function () {
            ngDialog.open({ scope: $scope, template: 'authDialog', showClose: false, controller: 'authDialogController'});
        };

        $scope.saveAuthJson = function () {
            if ($scope.auth.$valid) {
                $rootScope.saveAuthProccessed = true;
                $http.post('index.php/connect/save-auth-json', $scope.user)
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
                            $localStorage.isConnectAuthorized = true;
                        } else {
                            $scope.errors = true;
                            $localStorage.isConnectAuthorized = false;
                        }
                        $rootScope.isConnectAuthorized = $localStorage.isConnectAuthorized;
                        $rootScope.saveAuthProccessed = false;
                        $localStorage.connectUsername = $scope.user.username;
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
            $http.post('index.php/connect/remove-credentials', [])
                .success(function (response) {
                    if (response.success) {
                        $scope.logout = true;
                        $localStorage.isConnectAuthorized = $rootScope.isConnectAuthorized = false;
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
