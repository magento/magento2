/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-grid', ['ngStorage'])
    .controller('componentGridController', ['$rootScope', '$scope', '$http', '$localStorage', '$state',
        function ($rootScope, $scope, $http, $localStorage, $state) {
            $rootScope.componentsProcessed = false;
            $http.get('index.php/componentGrid/components').success(function(data) {
                $scope.components = data.components;
                $scope.displayComponents = data.components;
                $scope.total = data.total;
                if(typeof data.lastSyncData.lastSyncDate === "undefined") {
                    $scope.isOutOfSync = true;
                    $scope.countOfUpdate = 0;
                    $scope.countOfInstall = 0;
                } else {
                    $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
                    $scope.countOfUpdate = data.lastSyncData.countOfUpdate;
                    $scope.countOfInstall = data.lastSyncData.countOfInstall;
                    $scope.enabledInstall = data.lastSyncData.countOfInstall ? true : false;
                    $scope.isOutOfSync = false;
                }
                $scope.availableUpdatePackages = data.lastSyncData.packages;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);

                $rootScope.componentsProcessed = true;
            });

            $scope.$watch('currentPage + rowLimit', function() {
                var begin = (($scope.currentPage - 1) * $scope.rowLimit);
                var end = parseInt(begin) + parseInt(($scope.rowLimit));
                $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);
                if ($scope.components !== undefined) {
                    $scope.displayComponents = $scope.components.slice(begin, end);
                }
                if ($scope.currentPage > $scope.numberOfPages) {
                    $scope.currentPage = $scope.numberOfPages;
                }
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

            $scope.predicate = 'name';
            $scope.reverse = false;
            $scope.order = function(predicate) {
                $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
                $scope.predicate = predicate;
            };

            $scope.sync = function() {
                $scope.isHiddenSpinner = false;
                $http.get('index.php/componentGrid/sync').success(function(data) {
                    $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
                    $scope.availableUpdatePackages = data.lastSyncData.packages;
                    $scope.countOfUpdate = data.lastSyncData.countOfUpdate;
                    $scope.countOfInstall = data.lastSyncData.countOfInstall;
                    $scope.enabledInstall = data.lastSyncData.countOfInstall ? true : false;
                    $scope.isHiddenSpinner = true;
                    $scope.isOutOfSync = false;
                });
            };
            $scope.isAvailableUpdatePackage = function(packageName) {
                $localStorage.isConnectAuthorized = typeof $localStorage.isConnectAuthorized !== 'undefined' ? $localStorage.isConnectAuthorized : false;
                var isAvailable = typeof $scope.availableUpdatePackages !== 'undefined'
                    && $localStorage.isConnectAuthorized
                    && packageName in $scope.availableUpdatePackages;
                return isAvailable;
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
                } else if (component.disable === true) {
                    return indicators.off[type];
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

            $scope.enableDisable = function(type, component) {
                if (component.type.indexOf('module') >= 0 ) {
                    $localStorage.packages = [
                        {
                            name: component.moduleName
                        }
                    ];
                    if ($localStorage.titles[type].indexOf(component.moduleName) < 0 ) {
                        $localStorage.titles[type] = type.charAt(0).toUpperCase() + type.slice(1) + ' '
                            + component.moduleName;
                    }
                    $localStorage.componentType = component.type;
                    $localStorage.moduleName = component.moduleName;
                    $state.go('root.readiness-check-'+type);
                }
            };
            $scope.convertDate = function(date) {
                return new Date(date.replace(/-/g, '/'))
            }
        }
    ])
    .filter('startFrom', function() {
        return function(input, start) {
            if(input !== undefined && start !== 'NaN') {
                start = parseInt(start, 10);
                return input.slice(start);
            }
        }
    });
