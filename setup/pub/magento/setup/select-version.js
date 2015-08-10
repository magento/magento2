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
        $scope.upgradeReadyForNext = false;
        $scope.upgradeProcessed = false;
        $scope.upgradeProcessError = false;
        $scope.componentsReadyForNext = true;
        $scope.componentsProcessed = false;
        $scope.componentsProcessError = false;
        $scope.tryAgain = 0;

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
                $scope.tryAgain++;
            })
            .error(function (data) {
                $scope.upgradeProcessError = true;
                $scope.tryAgain++;
            });

        $scope.updateComponents = {
            yes: false,
            no: true
        };

        $scope.$watch('updateComponents.no', function() {

            if (angular.equals($scope.updateComponents.no, true)) {
                $scope.updateComponents.yes = false;
                if ($scope.tryAgain < 0) {
                    $scope.tryAgain++;
                }
            }
        });

        $scope.$watch('updateComponents.yes', function() {
            if (angular.equals($scope.updateComponents.yes, true)) {
                $scope.updateComponents.no = false;
                $scope.tryAgain--;
                if (!$scope.componentsProcessed && !$scope.componentsProcessError) {
                    $scope.componentsReadyForNext = false;
                    $http.get('index.php/other-components-grid/components', {'responseType': 'json'}).
                        success(function (data) {
                            if (data.responseType != 'error') {
                                $scope.components = data.components;
                                $scope.totalComponents = data.total;
                                $scope.totalForGrid = data.total;
                                var keys = Object.keys($scope.components);
                                for (var i = 0; i < $scope.totalForGrid; i++) {
                                    $scope.packages.push({
                                        name: keys[i],
                                        version: $scope.components[keys[i]].updates[0].id
                                    });
                                }
                                $scope.componentsReadyForNext = true;
                            } else {
                                $scope.componentsProcessError = true;
                            }
                            $scope.componentsProcessed = true;
                            $scope.tryAgain++;
                        })
                        .error(function (data) {
                            $scope.tryAgain++;
                            $scope.componentsProcessError = true;
                        });
                }
            }
        });

        $scope.setComponentVersion = function(name, $version) {
            for (var i = 0; i < $scope.totalForGrid; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages[i + 1].version = $version;
                }
            }
        };

        $scope.AddRemoveComponentOnSliderMove = function(name) {
            var found = false;
            for (var i = 0; i < $scope.totalForGrid; i++) {
                if ($scope.packages[i + 1].name === name) {
                    $scope.packages.splice(i + 1, 1);
                    $scope.totalForGrid = $scope.totalForGrid - 1;
                    found = true;
                }
            }
            if (!found) {
                $scope.packages.push({
                    name: name,
                    version: $scope.components[name].dropdownId
                });
                $scope.totalForGrid = $scope.totalForGrid + 1;
            }
        };

        $scope.update = function() {
            $scope.packages[0].version = $scope.selectedOption;
            if (angular.equals($scope.updateComponents.no, true)) {
                if ($scope.totalForGrid > 0) {
                    $scope.packages.splice(1, $scope.totalForGrid);
                }
            }
            $localStorage.packages = $scope.packages;
            $scope.nextState();
        };
    }]);
