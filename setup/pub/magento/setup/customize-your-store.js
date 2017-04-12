/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage', 'ngSanitize'])
    .controller('customizeYourStoreController', ['$scope', '$localStorage' , '$state', '$http', '$sce', function ($scope, $localStorage, $state, $http, $sce) {
        $scope.store = {
            timezone: 'UTC',
            currency: 'USD',
            language: 'en_US',
            useSampleData: false,
            cleanUpDatabase: false,
            loadedAllModules: false,
            showModulesControl: false,
            selectAll: true,
            allModules: [],
            errorFlag : false,
            showError: false,
            selectedModules : [],
            disabledModules: [],
            errorMessage: '',
            force: false,
            advanced: {
                expanded: false
            }
        };

        $scope.loading = false;

        if (!$localStorage.store) {
            $http.get('index.php/customize-your-store/default-time-zone',{'responseType' : 'json'})
                .success(function (data) {
                    $scope.store.timezone = data.defaultTimeZone;
                })
                .error(function (data) {
                    $scope.store.timezone = 'UTC';
                });
        }

        if ($localStorage.store) {
            $scope.store = $localStorage.store;
        }

        $scope.checkModuleConstraints = function () {
            $state.loadModules();
            $localStorage.store = $scope.store;
            $scope.loading = true;
            $http.post('index.php/modules/all-modules-valid', $scope.store)
                .success(function (data) {
                    $scope.checkModuleConstraints.result = data;
                    if (($scope.checkModuleConstraints.result !== undefined) && ($scope.checkModuleConstraints.result.success)) {
                        $scope.loading = false;
                        $scope.nextState();
                    } else {
                        $scope.store.errorMessage = $sce.trustAsHtml($scope.checkModuleConstraints.result.error);
                        $scope.loading = false;
                    }
                });
        };

        if (!$scope.store.loadedAllModules) {
            $http.get('index.php/modules').success(function (data) {
                $state.loadedModules = data;
                $scope.store.showModulesControl = true;
                if (data.error) {
                    $scope.updateOnExpand($scope.store.advanced);
                    $scope.store.errorMessage = $sce.trustAsHtml(data.error);
                }
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
                    if(allModules[eachModule].disabled) {
                        $scope.store.disabledModules.push(allModules[eachModule].name);
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

        $scope.expandError = function() {
            $scope.store.errorFlag = !$scope.store.errorFlag;
        };

        $scope.toggleForce = function() {
            $scope.force = !$scope.force;
        };

        $scope.toggleModule = function(module) {
            var idx = $scope.store.selectedModules.indexOf(module);
            if (idx > -1) {
                $scope.store.selectedModules.splice(idx, 1);
            } else {
                $scope.store.selectedModules.push(module);
            }
            $scope.checkIfAllAreSelected();
            $scope.validateModules(module);
        };

        $scope.validateModules = function(module){
            if ($scope.force) return;
            // validate enabling disabling here.
            var idx = $scope.store.selectedModules.indexOf(module);
            var moduleStatus = (idx > -1) ? true : false;
            var allParameters = {'allModules' : $scope.store.allModules, 'selectedModules' : $scope.store.selectedModules, 'module' : module, 'status' : moduleStatus};

            $http.post('index.php/modules/validate', allParameters)
                .success(function (data) {
                    $scope.checkModuleConstraints.result = data;
                    if ((($scope.checkModuleConstraints.result.error !== undefined) && (!$scope.checkModuleConstraints.result.success))) {
                        $scope.store.errorMessage = $sce.trustAsHtml($scope.checkModuleConstraints.result.error);
                        if (moduleStatus) {
                            $scope.store.selectedModules.splice(idx, 1);
                        } else {
                            $scope.store.selectedModules.push(module);
                        }
                    } else {
                        $state.loadedModules = data;
                        $scope.store.errorMessage = false;
                        $scope.store.showError = false;
                        $scope.store.errorFlag = false;
                        $scope.store.loadedAllModules = false;
                        $scope.store.allModules =[];
                        $scope.store.selectedModules =[];
                        $scope.store.disabledModules =[];
                        $state.loadModules();
                    }
                });

        }

        $scope.toggleAllModules = function() {
            $scope.store.selectAll = !$scope.store.selectAll;
            if ($scope.store.selectAll) {
                for(var i = 0; i < $scope.store.allModules.length; i++) {
                    $scope.store.selectedModules[i] = $scope.store.allModules[i];
                }
            } else {
                for(var i = 0; i < $scope.store.allModules.length; i++) {
                    var idx = $scope.store.selectedModules.indexOf($scope.store.allModules[i]);
                    if ($scope.store.disabledModules.indexOf($scope.store.allModules[i]) < 0) {
                        $scope.store.selectedModules.splice(idx, 1);
                    }
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
