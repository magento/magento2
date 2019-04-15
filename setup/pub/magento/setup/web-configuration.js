/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('web-configuration', ['ngStorage'])
    .controller(
        'webConfigurationController',
        ['$scope', '$state', '$localStorage', '$http', function ($scope, $state, $localStorage, $http) {
        $scope.config = {
            address: {
                base_url: '',
                auto_base_url: '',
                actual_base_url: '',
                admin: ''
            },
            https: {
                front: false,
                admin: false,
                text: ''
            },
            rewrites: {
                allowed: true
            },
            encrypt: {
                key: null,
                type: 'magento'
            },
            advanced: {
                expanded: false
            },
            sessionSave: {
                type: 'files',
                error: false
            }
        };

        if ($localStorage.config) {
            $scope.config = $localStorage.config;
        }

        $scope.$on('nextState', function () {
            $localStorage.config = $scope.config;
        });

        $scope.updateOnExpand = function(obj) {
            obj.expanded = !obj.expanded;
        };

        $scope.fillBaseURL = function() {
            if (angular.equals($scope.config.address.base_url, '')) {
                $scope.config.address.base_url = $scope.config.address.auto_base_url;
            }
        };

        $scope.$watch('config.address.base_url', function() {
            if (angular.equals($scope.config.address.base_url, '')) {
                $scope.config.address.actual_base_url = $scope.config.address.auto_base_url;
            } else {
                $scope.config.address.actual_base_url = $scope.config.address.base_url;
            }
        });

        $scope.$watch('config.encrypt.type', function() {
            if (angular.equals($scope.config.encrypt.type, 'magento')) {
                $scope.config.encrypt.key = null;
            }
        });

        $scope.$watch('config.address.base_url', function() {
            if (angular.equals($scope.config.https.text, '') || angular.isUndefined($scope.config.https.text)) {
                $scope.config.https.text = $scope.config.address.base_url.replace('http', 'https');
            }
        });

        $scope.populateHttps = function() {
            $scope.config.https.text = $scope.config.address.base_url.replace('http', 'https');
        };

        $scope.showEncryptKey = function() {
            return angular.equals($scope.config.encrypt.type, 'user');
        };

        $scope.showHttpsField = function() {
            return ($scope.config.https.front || $scope.config.https.admin);
        };

        $scope.addSlash = function() {
            if (angular.isUndefined($scope.config.address.base_url)) {
                return;
            }

            var p = $scope.config.address.base_url;
            if (p.length > 1) {
                var lastChar = p.substr(-1);
                if (lastChar != '/') {
                    $scope.config.address.base_url = p + '/';
                }
            }
        };

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.webconfig.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.webconfig.submitted = true;
            }
        };

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.webconfig.$valid }, function(valid) {
            if (valid) {
                $scope.webconfig.submitted = false;
            }
        });

        // Validate URL
        $scope.validateUrl = function () {
            if (!$scope.webconfig.submitted) {
                $http.post('index.php/url-check', $scope.config)
                    .then(function successCallback(resp) {
                        $scope.validateUrl.result = resp.data;

                        if ($scope.validateUrl.result.successUrl && $scope.validateUrl.result.successSecureUrl) {
                            $scope.nextState();
                        }

                        if (!$scope.validateUrl.result.successUrl) {
                            $scope.webconfig.submitted = true;
                            $scope.webconfig.base_url.$setValidity('url', false);
                        }

                        if (!$scope.validateUrl.result.successSecureUrl) {
                            $scope.webconfig.submitted = true;
                            $scope.webconfig.https.$setValidity('url', false);
                        }
                    }, function errorCallback(resp) {
                        $scope.validateUrl.failed = resp.data;
                    });
            }
        };
    }]);
