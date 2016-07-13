/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('module-grid', ['ngStorage'])
    .controller('moduleGridController', ['$rootScope', '$scope', '$http', '$localStorage', '$state',
        function ($rootScope, $scope, $http, $localStorage, $state) {
            $rootScope.modulesProcessed = false;
            $http.get('index.php/moduleGrid/modules').success(function(data) {
                $scope.modules = data.modules;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 200;
                $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);
                $rootScope.modulesProcessed = true;
            });

            $scope.$watch('currentPage + rowLimit', function() {
                var begin = (($scope.currentPage - 1) * $scope.rowLimit);
                var end = parseInt(begin) + parseInt(($scope.rowLimit));
                $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);
                if ($scope.currentPage > $scope.numberOfPages) {
                    $scope.currentPage = $scope.numberOfPages;
                }
            });

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

            $scope.getIndicatorInfo = function(component, type) {
                var indicators = {
                    'on' : {'icon' : '_on', 'label' : 'On'},
                    'off' : {'icon' : '_off', 'label' : 'Off'}
                };

                var types = ['label', 'icon'];

                if (types.indexOf(type) == -1) {
                    type = 'icon';
                }

                if (component.enable === true) {
                    return indicators.on[type];
                }

                return indicators.off[type];
            };

            $scope.enableDisable = function(type, component) {
                if (component.type.indexOf('module') >= 0 ) {
                    $localStorage.packages = [
                        {
                            name: component.moduleName
                        }
                    ];
                    if (component.moduleName) {
                        $localStorage.moduleName = component.moduleName;
                    } else {
                        $localStorage.moduleName = component.name;
                    }
                    if ($localStorage.titles[type].indexOf($localStorage.moduleName) < 0 ) {
                        $localStorage.titles[type] = type.charAt(0).toUpperCase() + type.slice(1) + ' '
                            + $localStorage.moduleName;
                    }
                    $rootScope.titles = $localStorage.titles;
                    $localStorage.componentType = component.type;
                    $state.go('root.readiness-check-'+type);
                }
            };
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
