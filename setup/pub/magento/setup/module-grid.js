/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('module-grid', ['ngStorage'])
    .controller('moduleGridController', ['$rootScope', '$scope', '$http', '$localStorage', '$state', 'titleService', 'paginationService',
        function ($rootScope, $scope, $http, $localStorage, $state, titleService, paginationService) {
            $rootScope.modulesProcessed = false;
            $http.get('index.php/moduleGrid/modules').success(function(data) {
                $scope.modules = data.modules;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total/$scope.rowLimit);
                $rootScope.modulesProcessed = true;
            });

            paginationService.initWatchers($scope);

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
                $localStorage.packages = [
                    {
                        name: component.moduleName,
                        isComposerPackage: component.name !== 'unknown',
                    }
                ];
                titleService.setTitle(type, component);
                $localStorage.componentType = component.type;
                $state.go('root.readiness-check-'+type);
            };
        }
    ]);
