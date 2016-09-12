/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

angular.module('extension-grid', ['ngStorage'])
    .controller('extensionGridController', [
        '$rootScope', '$scope', '$http', '$localStorage', '$state','titleService', 'authService', 'paginationService',
        function ($rootScope, $scope, $http, $localStorage, $state, titleService, authService, paginationService) {
            authService.checkMarketplaceAuthorized();
            $rootScope.extensionsProcessed = false;
            $scope.syncError = false;
            $scope.currentPage = 1;

            $http.get('index.php/extensionGrid/extensions').success(function (data) {
                $scope.extensions = data.extensions;
                $scope.total = data.total;

                if (data.error !== '') {
                    $scope.syncError = true;
                    $scope.ErrorMessage = data.error;
                }

                if (typeof data.lastSyncData.lastSyncDate === 'undefined') {
                    $scope.isOutOfSync = true;
                    $scope.countOfUpdate = 0;
                    $scope.countOfInstall = 0;
                } else {
                    $scope.lastSyncDate = data.lastSyncData.lastSyncDate.date;
                    $scope.lastSyncTime = data.lastSyncData.lastSyncDate.time;
                    $scope.countOfUpdate = data.lastSyncData.countOfUpdate;
                    $scope.countOfInstall = data.lastSyncData.countOfInstall;
                    $scope.enabledInstall = data.lastSyncData.countOfInstall ? true : false;
                    $scope.isOutOfSync = false;
                }
                $scope.availableUpdatePackages = data.lastSyncData.packages;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
                $rootScope.extensionsProcessed = true;
            });

            paginationService.initWatchers($scope);

            $scope.isOutOfSync = false;
            $scope.isHiddenSpinner = true;
            $scope.selectedExtension = null;

            $scope.reset = function () {
                authService.reset({
                    success: function() {
                        $scope.logout = true;
                        authService.checkMarketplaceAuthorized();
                    }
                })
            };

            $scope.isActiveActionsCell = function(extension) {
                return $scope.selectedExtension === extension;
            };

            $scope.toggleActiveActionsCell = function(extension) {
                $scope.selectedExtension = $scope.selectedExtension == extension ? null : extension;
            };

            $scope.closeActiveActionsCell = function(extension) {
                $scope.toggleActiveActionsCell(extension);
            };

            $scope.predicate = 'name';
            $scope.reverse = false;
            $scope.order = function(predicate) {
                $scope.reverse = $scope.predicate === predicate ? !$scope.reverse : false;
                $scope.predicate = predicate;
            };

            $scope.sync = function() {
                $scope.isHiddenSpinner = false;
                $http.get('index.php/extensionGrid/sync').success(function(data) {
                    if (typeof data.lastSyncData.lastSyncDate !== 'undefined') {
                        $scope.lastSyncDate = data.lastSyncData.lastSyncDate.date;
                        $scope.lastSyncTime = data.lastSyncData.lastSyncDate.time;
                    }

                    if (data.error !== '') {
                        $scope.syncError = true;
                        $scope.ErrorMessage = data.error;
                    }
                    $scope.availableUpdatePackages = data.lastSyncData.packages;
                    $scope.countOfUpdate = data.lastSyncData.countOfUpdate;
                    $scope.countOfInstall = data.lastSyncData.countOfInstall;
                    $scope.enabledInstall = data.lastSyncData.countOfInstall ? true : false;
                    $scope.isHiddenSpinner = true;
                    $scope.isOutOfSync = false;
                });
            };
            $scope.isAvailableUpdatePackage = function(packageName) {
                $localStorage.isMarketplaceAuthorized = typeof $localStorage.isMarketplaceAuthorized !== 'undefined' ? $localStorage.isMarketplaceAuthorized : false;
                var isAvailable = typeof $scope.availableUpdatePackages !== 'undefined'
                    && $localStorage.isMarketplaceAuthorized
                    && packageName in $scope.availableUpdatePackages;
                return isAvailable;
            };

            $scope.getIndicatorInfo = function (extension, type) {
                var indicators = {
                    'info': {
                        'icon': '_info', 'label': 'Update Available'
                    }
                };

                var types = ['label', 'icon'];

                if (types.indexOf(type) === -1) {
                    type = 'icon';
                }

                if ($scope.isAvailableUpdatePackage(extension.name)) {
                    return indicators.info[type];
                }
            };

            $scope.update = function(extension) {
                $localStorage.packages = [
                    {
                        name: extension.name,
                        version: $scope.availableUpdatePackages[extension.name]['latestVersion']
                    }
                ];
                titleService.setTitle('update', extension);
                $state.go('root.readiness-check-update');
            };

            $scope.uninstall = function(extension) {
                $localStorage.packages = [
                    {
                        name: extension.name
                    }
                ];
                titleService.setTitle('uninstall', extension);
                $localStorage.componentType = extension.type;
                $state.go('root.readiness-check-uninstall');
            };
        }
    ]);
