/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('install-extension-grid', ['ngStorage', 'clickOut'])
    .controller('installExtensionGridController', ['$scope', '$http', '$localStorage', 'authService', 'paginationService',
        function ($scope, $http, $localStorage, authService, paginationService) {

            $http.get('index.php/installExtensionGrid/extensions').success(function(data) {
                $scope.error = false;
                $scope.errorMessage = '';
                $scope.selectedExtensions = {};
                $scope.allExtensions = {};
                angular.forEach(data.extensions, function(value) {
                    this[value.name] = {
                        'name': value.name,
                        'version': value.version
                    };
                }, $scope.allExtensions);
                $scope.extensions = data.extensions;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
            });

            paginationService.initWatchers($scope);

            $scope.updateSelectedExtensions = function($event, name, version) {
                var checkbox = $event.target;
                if (checkbox.checked) {
                    $scope.selectedExtensions[name] = {
                        'name': name,
                        'version': version
                    };
                    if ($scope.getObjectSize($scope.selectedExtensions) == $scope.getObjectSize($scope.allExtensions)) {
                        $scope.someExtensionsSelected = false;
                        $scope.allExtensionsSelected = true;
                    } else {
                        $scope.someExtensionsSelected = true;
                        $scope.allExtensionsSelected = false;
                    }
                } else {
                    delete $scope.selectedExtensions[name];
                    $scope.allExtensionsSelected = false;
                    if ($scope.getObjectSize($scope.selectedExtensions) > 0) {
                        $scope.someExtensionsSelected = true;
                    } else {
                        $scope.someExtensionsSelected = false;
                    }
                }
            };

            $scope.predicate = 'name';
            $scope.reverse = false;
            $scope.order = function(predicate) {
                $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
                $scope.predicate = predicate;
            };

            $scope.getObjectSize = function(obj) {
                var size = 0, key;
                for (key in obj) {
                    if (obj.hasOwnProperty(key)) {
                        ++size;
                    }
                }
                return size;
            };

            $scope.isNewExtensionsMenuVisible = false;
            $scope.toggleNewExtensionsMenu = function() {
                $scope.isNewExtensionsMenuVisible = !$scope.isNewExtensionsMenuVisible;
            };
            $scope.hideNewExtensionsMenu = function() {
                $scope.isNewExtensionsMenuVisible = false;
            };
            $scope.someExtensionsSelected = false;
            $scope.allExtensionsSelected = false;
            $scope.selectAllExtensions = function() {
                $scope.isNewExtensionsMenuVisible = false;
                $scope.someExtensionsSelected = false;
                $scope.allExtensionsSelected = true;
                $scope.selectedExtensions = angular.copy($scope.allExtensions);
            };
            $scope.deselectAllExtensions = function() {
                $scope.isNewExtensionsMenuVisible = false;
                $scope.someExtensionsSelected = false;
                $scope.allExtensionsSelected = false;
                $scope.selectedExtensions = {};
            };

            $scope.isHiddenSpinner = true;
            $scope.installAll = function() {
                $scope.isHiddenSpinner = false;
                authService.checkAuth({
                    success: function(response) {
                        $scope.isHiddenSpinner = true;
                        if ($scope.getObjectSize($scope.selectedExtensions) > 0) {
                            $scope.error = false;
                            $scope.errorMessage = '';
                            $localStorage.packages = $scope.selectedExtensions;
                        } else {
                            $scope.error = true;
                            $scope.errorMessage = 'Please select at least one extension';
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
