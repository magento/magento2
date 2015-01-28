/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage'])
    .controller('customizeYourStoreController', ['$scope', '$localStorage', function ($scope, $localStorage) {
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
    }]);
