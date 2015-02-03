/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage'])
    .controller('customizeYourStoreController', ['$scope', '$localStorage' , '$state', '$http', '$sce', function ($scope, $localStorage, $state, $http, $sce) {
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

        $scope.loading = false;

        if ($localStorage.store) {
            $scope.store = $localStorage.store;
        }

        $scope.checkModuleConstraints = function () {
            if (!$scope.store.loadedAllModules) {
                $state.loadModules();
            }
            $localStorage.store = $scope.store;
            $scope.loading = true;
            $http.post('index.php/module-check', $scope.store)
                .success(function (data) {
                    $scope.checkModuleConstraints.result = data;
                    if (($scope.checkModuleConstraints.result !== undefined) && ($scope.checkModuleConstraints.result.success)) {
                        $scope.loading = false;
                        $scope.nextState();
                    } else {
                        $scope.checkModuleConstraints.result.error = $sce.trustAsHtml($scope.checkModuleConstraints.result.error);
                        $scope.loading = false;
                    }
                });
        };

        if (!$scope.store.loadedAllModules) {
            $http.get('index.php/modules').success(function (data) {
                $state.loadedModules = data;
            });
        }

        $state.loadModules = function(){
            if(!$scope.store.loadedAllModules) {
                var allModules = $scope.$state.loadedModules.modules;
                for (var eachModule in allModules) {
                    $scope.store.allModules.push(allModules[eachModule].name);
                    if(allModules[eachModule].selected) {
                        $scope.store.selectedModules.push(allModules[eachModule].name);
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
                for(var i = 0; i < $scope.store.allModules.length; i++) {
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

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.customizeStore.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.customizeStore.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.customizeStore.$valid }, function(valid) {
            if (valid) {
                $scope.customizeStore.submitted = false;
            }
        });
    }])
    ;
