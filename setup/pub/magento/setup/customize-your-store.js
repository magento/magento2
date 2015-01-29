/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage'])
    .controller('customizeYourStoreController', ['$scope', '$localStorage' , '$state', 'moduleService', function ($scope, $localStorage, $state, moduleService) {
        $scope.store = {
            timezone: 'America/Los_Angeles',
            currency: 'USD',
            language: 'en_US',
            useSampleData: false,
            loadedAllModules: false,
            selectAll: true,
            allModules: [],
            selectedModules : [],
            advanced: {
                expanded: false
            }
        };

        if ($localStorage.store) {
            $scope.store = $localStorage.store;
        }

        if (!$scope.store.loadedAllModules) {
            moduleService.load();
        }
        
        $scope.$on('nextState', function () {
            if (!$scope.store.loadedAllModules) {
                $state.loadModules();
            }
            $localStorage.store = $scope.store;
        });

        $state.loadModules= function(){
            if(!$scope.store.loadedAllModules) {
                var allModules = $scope.$state.loadedModules.modules;
                for(var i=0;i<allModules.length;i++) {
                    $scope.store.allModules.push(allModules[i].name);
                    if(allModules[i].selected) {
                        $scope.store.selectedModules.push(allModules[i].name);
                    }
                }
                $scope.store.loadedAllModules = true;
                $scope.checkIfAllAreSelected();
            }
        }

        $scope.updateOnExpand = function(obj) {
            $state.loadModules();
            obj.expanded = !obj.expanded;
        };

        $scope.toggleModule = function(module) {
            var idx = $scope.store.selectedModules.indexOf(module);
            if (idx > -1) {
                $scope.store.selectedModules.splice(idx, 1);
            } else {
                $scope.store.selectedModules.push(module);
            }
            $scope.checkIfAllAreSelected();
            $scope.validateModules();
        };

        $scope.validateModules = function(){
            // validate enabling disabling here.
        }

        $scope.toggleAllModules = function() {
            $scope.store.selectAll = !$scope.store.selectAll;
            $scope.store.selectedModules = [];
            if ($scope.store.selectAll) {
                for(var i=0;i<$scope.store.allModules.length;i++) {
                    $scope.store.selectedModules[i] = $scope.store.allModules[i];
                }
            }
        };

        $scope.checkIfAllAreSelected = function() {
            if ($scope.store.selectedModules.length === $scope.store.allModules.length &&
                $scope.store.selectedModules.length !== 0 ) {
                $scope.store.selectAll = true;
            } else {
                $scope.store.selectAll = false;
            }
        }
    }])
    .service('moduleService', ['$state', '$http', function ( $state, $http) {
        return {
            mainState: {},
            states: [],
            load: function () {
                $http.get('index.php/modules').success(function (data) {
                    $state.loadedModules = data;
                });
            }
        }
    }]);
