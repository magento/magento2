/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('select-version', ['ngStorage'])
    .controller('selectVersionController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
        $scope.packages = [{
            name: '',
            version: ''
        }];

        $scope.upgradeProcessed = false;
        $scope.upgradeProcessError = false;
        $scope.upgradeReadyForNext = false;
        $scope.componentsProcessed = false;
        $scope.componentsProcessError = false;
        $scope.componentsReadyForNext = false;

        $http.get('index.php/select-version/systemPackage', {'responseType' : 'json'})
            .success(function (data) {
                if (data.responseType != 'error') {
                    $scope.versions = data.package.versions;
                    $scope.packages[0].name = data.package.package;
                    $scope.packages[0].version = $scope.versions[0].id;
                    $scope.selectedOption = $scope.versions[0].id;
                    $scope.upgradeReadyForNext = true;
                } else {
                    $scope.upgradeProcessError = true;
                }
                $scope.upgradeProcessed = true;
            })
            .error(function (data) {
                $scope.upgradeProcessError = true;
            });

        $scope.choice = {
            yes: false,
            no: true
        };
        $scope.$watch('choice.no', function() {
            if (angular.equals($scope.choice.no, true)) {
                $scope.choice.yes = false;
                $scope.componentsProcessed = false;
                $scope.componentsProcessError = false;
                $scope.componentsReadyForNext = false;
            }
        });

        $scope.$watch('choice.yes', function() {
            if (angular.equals($scope.choice.yes, true)) {
                $http.get('index.php/select-version/components', {'responseType' : 'json'}).
                    success(function(data) {
                        if (data.responseType != 'error') {
                            $scope.components = data.components;
                            $scope.total = data.total;
                            var keys = Object.keys($scope.components);
                            for (var i = 0; i < $scope.total; i++) {
                                $scope.packages.push({name: keys[i], version: $scope.components[keys[i]].upgrades[0]});
                            }
                            $scope.componentsReadyForNext = true;
                        } else {
                            $scope.componentsProcessError = true;
                        }
                        $scope.componentsProcessed = true;
                    })
                    .error(function (data) {
                        $scope.componentsProcessError = true;
                    });
                $scope.choice.no = false;
            }
        });

        $scope.updatePackages = function(name, upgrade) {
            for (var i = 0; i < $scope.total; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages[i + 1].version = upgrade;
                }
            }
        };

        $scope.update = function(component) {
            $scope.packages[0].version = $scope.selectedOption;
            if (angular.equals($scope.choice.no, true)) {
                for (var i = 0; i < $scope.total; i++) {
                    $scope.packages.splice(i + 1, $scope.total);
                }
            } else {
                for (var i = 0; i < $scope.total; i++) {
                    if ($scope.packages[i + 1].version.indexOf(" (latest)") > -1) {
                        $scope.packages[i + 1].version = $scope.packages[i + 1].version.replace(" (latest)", "");
                    }
                }
            }
            $localStorage.packages = $scope.packages;
            $scope.nextState();
        };
    }]);
