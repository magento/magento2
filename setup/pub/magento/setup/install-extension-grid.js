/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('install-extension-grid', ['ngStorage', 'clickOut'])
    .controller('installExtensionGridController', ['$scope', '$http', 'ngDialog', '$localStorage', '$rootScope',
        function ($scope, $http, ngDialog, $localStorage, $rootScope) {

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
            $scope.start = 0;
            $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
        });

        $scope.open = function() {
            ngDialog.open({ scope: $scope, template: 'authDialog', showClose: false, controller: 'authDialogController' });
        };

        $scope.recalculatePagination = function(currentPage, rowLimit) {
            $scope.currentPage = parseInt(currentPage, 10);
            $scope.rowLimit = parseInt(rowLimit, 10);
            $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
            if ($scope.currentPage > $scope.numberOfPages) {
                $scope.currentPage = $scope.numberOfPages;
            }
            $scope.start = ($scope.currentPage - 1) * $scope.rowLimit;
        };

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
            $scope.checkAuth();
            $localStorage.isMarketplaceAuthorized = typeof $localStorage.isMarketplaceAuthorized !== 'undefined' ? $localStorage.isMarketplaceAuthorized : false;
            if ($localStorage.isMarketplaceAuthorized === false) {
                $scope.open();
            } else {
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
            }
        };

        $scope.install = function(extension) {
            $scope.checkAuth();
            $localStorage.isMarketplaceAuthorized = typeof $localStorage.isMarketplaceAuthorized !== 'undefined' ? $localStorage.isMarketplaceAuthorized : false;
            if ($localStorage.isMarketplaceAuthorized === false) {
                $scope.open();
            } else {
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
                    $scope.error = false;
                    $scope.errorMessage = '';
                }

                if (!$scope.error) {
                    $scope.nextState();
                }
            }
        };

        $scope.checkAuth = function() {
            $http.post('index.php/marketplace/check-auth', [])
            .success(function (response) {
                if (response.success) {
                    $localStorage.isMarketplaceAuthorized = true;
                } else {
                    $localStorage.isMarketplaceAuthorized = false;
                }
            })
            .error(function() {
                $localStorage.isMarketplaceAuthorized = false;
                $scope.error = true;
                $scope.errorMessage = 'Internal server error';
            });
        };
    }])
    .filter('startFrom', function() {
        return function(input, start) {
            if (input !== undefined && start !== 'NaN') {
                start = parseInt(start, 10);
                return input.slice(start);
            }
        }
    });
