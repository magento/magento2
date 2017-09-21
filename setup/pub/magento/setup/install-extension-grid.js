/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('install-extension-grid', ['ngStorage', 'clickOut'])
    .controller('installExtensionGridController', ['$scope', '$http', '$localStorage', 'authService', 'paginationService', 'multipleChoiceService',
        function ($scope, $http, $localStorage, authService, paginationService, multipleChoiceService) {

            $http.get('index.php/installExtensionGrid/extensions').success(function(data) {
                $scope.error = false;
                $scope.errorMessage = '';
                $scope.multipleChoiceService = multipleChoiceService;
                $scope.multipleChoiceService.reset();
                angular.forEach(data.extensions, function(value) {
                    $scope.multipleChoiceService.addExtension(value.name, value.version);
                });
                $scope.extensions = data.extensions;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
            });

            paginationService.initWatchers($scope);

            $scope.predicate = 'name';
            $scope.reverse = false;
            $scope.order = function(predicate) {
                $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
                $scope.predicate = predicate;
            };

            $scope.isHiddenSpinner = true;
            $scope.installAll = function() {
                $scope.isHiddenSpinner = false;
                authService.checkAuth({
                    success: function(response) {
                        $scope.isHiddenSpinner = true;
                        var result = $scope.multipleChoiceService.checkSelectedExtensions();
                        $scope.error = result.error;
                        $scope.errorMessage = result.errorMessage;

                        if (!$scope.error) {
                            $scope.nextState();
                        }
                    },
                    fail: function(response) {
                        $scope.isHiddenSpinner = true;
                        authService.openAuthDialog($scope);
                    },
                    error: function() {
                        $scope.isHiddenSpinner = true;
                        $scope.error = true;
                        $scope.errorMessage = 'Internal server error';
                    }
                });
            };

            $scope.install = function(extension) {
                $scope.isHiddenSpinner = false;
                authService.checkAuth({
                    success: function(response) {
                        $scope.isHiddenSpinner = true;
                        if (extension === 'undefined') {
                            $scope.error = true;
                            $scope.errorMessage = 'No extensions for install';
                        } else {
                            $localStorage.packages = [
                                {
                                    name: extension.name,
                                    version: extension.version
                                }
                            ];
                            $localStorage.moduleName = extension.name;
                            $localStorage.packageTitle = extension.package_title;
                            $scope.error = false;
                            $scope.errorMessage = '';
                        }

                        if (!$scope.error) {
                            $scope.nextState();
                        }
                    },
                    fail: function(response) {
                        authService.openAuthDialog($scope);
                    },
                    error: function() {
                        $scope.isHiddenSpinner = true;
                        $scope.error = true;
                        $scope.errorMessage = 'Internal server error';
                    }
                });
            };
        }
    ]);
