/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-grid', ['ngStorage'])
    .controller('componentGridController', ['$scope', '$http', '$localStorage', '$state',
        function ($scope, $http, $localStorage, $state) {
            $scope.componentsProcessed = false;
            $http.get('index.php/componentGrid/components').success(function(data) {
                $scope.components = data.components;
                $scope.total = data.total;
                if(typeof data.lastSyncData.lastSyncDate === "undefined") {
                    $scope.isOutOfSync = true;
                } else {
                    $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
                    $scope.isOutOfSync = false;
                }
                $scope.availableUpdatePackages = data.lastSyncData.packages;
                $scope.currentPage = 0;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil(data.total/$scope.rowLimit);
                $scope.componentsProcessed = true;
            });

            $scope.isOutOfSync = false;
            $scope.isHiddenSpinner = true;
            $scope.selectedComponent = null;

            $scope.isActiveActionsCell = function(component) {
                return $scope.selectedComponent === component;
            };

            $scope.toggleActiveActionsCell = function(component) {
                $scope.selectedComponent = $scope.selectedComponent == component ? null : component;
            };

            $scope.closeActiveActionsCell = function(component) {
                $scope.toggleActiveActionsCell(component);
            };

            $scope.sync = function() {
                $scope.isHiddenSpinner = false;
                $http.get('index.php/componentGrid/sync').success(function(data) {
                    $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
                    $scope.availableUpdatePackages = data.lastSyncData.packages;
                    $scope.isHiddenSpinner = true;
                    $scope.isOutOfSync = false;
                });
            };

            $scope.isAvailableUpdatePackage = function(packageName) {
                return typeof $scope.availableUpdatePackages !== 'undefined'
                    && packageName in $scope.availableUpdatePackages;
            };

            $scope.getIndicatorInfo = function(component, type) {
                var indicators = {
                    'info' : {'icon' : '_info', 'label' : 'Update Available'},
                    'on' : {'icon' : '_on', 'label' : 'On'},
                    'off' : {'icon' : '_off', 'label' : 'Off'}
                };

                var types = ['label', 'icon'];

                if (types.indexOf(type) == -1) {
                    type = 'icon';
                }

                if ($scope.isAvailableUpdatePackage(component.name)) {
                    return indicators.info[type];
                }
                return indicators.on[type];
            };

            $scope.update = function(component) {
                $localStorage.packages = [
                    {
                        name: component.name,
                        version: $scope.availableUpdatePackages[component.name]['latestVersion']
                    }
                ];
                if ($localStorage.titles['update'].indexOf(component.moduleName) < 0 ) {
                    $localStorage.titles['update'] = 'Update ' + component.moduleName;
                }
                $localStorage.moduleName = component.moduleName;
                $scope.nextState();
            };

            $scope.uninstall = function(component) {
                $localStorage.packages = [
                    {
                        name: component.name
                    }
                ];
                if ($localStorage.titles['uninstall'].indexOf(component.moduleName) < 0 ) {
                    $localStorage.titles['uninstall'] = 'Uninstall ' + component.moduleName;
                }
                $localStorage.componentType = component.type;
                $localStorage.moduleName = component.moduleName;
                $state.go('root.readiness-check-uninstall');
            };

            $scope.convertDate = function(date) {
                return new Date(date);
            }
        }])
    .filter('startFrom', function() {
        return function(input, start) {
            if(input !== undefined && start !== 'NaN') {
                start = parseInt(start, 10);
                return input.slice(start);
            }
        }
    })
;
