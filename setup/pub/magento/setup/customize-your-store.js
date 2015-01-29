/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage'])
    .controller('customizeYourStoreController', ['$scope', '$state', '$localStorage', '$http', function ($scope, $state, $localStorage, $http) {
        $scope.store = {
            timezone: 'America/Los_Angeles',
            currency: 'USD',
            language: 'en_US',
            useSampleData: false,
            selectAll: false,
            allModules: [],
            selectedModules : [],
            advanced: {
                expanded: false
            }
        };

        if ($localStorage.store) {
            $scope.store = $localStorage.store;
        }

        $scope.checkModuleConstraints = function () {
            $http.post('index.php/module-check', $scope.store)
                .success(function (data) {
                    $scope.checkModuleConstraints.result = data;
                    if (!(($scope.checkModuleConstraints.result !== undefined) && (!$scope.checkModuleConstraints.result.success))) {
                        $scope.nextState();
                    }
                });
        };

        $scope.$on('nextState', function () {
            $localStorage.store = $scope.store;
        });

        $scope.updateOnExpand = function(obj) {
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
            if (!$scope.store.selectAll) {
                $scope.store.selectedModules = $scope.store.allModules;
                $scope.store.selectAll = true;
            } else {
                $scope.store.selectedModules = [];
                $scope.store.selectAll = false;
            }
        };

        $scope.checkIfAllAreSelected = function() {
            if ($scope.store.selectedModules.length === $scope.store.allModules.length) {
                $scope.store.selectAll = true;
            } else {
                $scope.store.selectAll = false;
            }
        }

        $scope.add = function(value, selected) {
            var addToArray=true;
            for(var i=0;i<$scope.store.allModules.length;i++) {
                if($scope.store.allModules[i]===value) {
                    addToArray=false;
                    break;
                }
            }
            if(addToArray) {
                if(selected) {
                    $scope.store.selectedModules.push(value);
                }
                $scope.store.allModules.push(value);
            }
        };

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
    }]);
