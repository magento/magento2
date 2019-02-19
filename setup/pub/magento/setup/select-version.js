/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('select-version', ['ngStorage'])
    .controller('selectVersionController', ['$scope', '$http', '$localStorage', '$sce', function ($scope, $http, $localStorage, $sce) {
        $scope.packages = [{
            name: '',
            version: ''
        }];
        $scope.upgradeReadyForNext = false;
        $scope.upgradeProcessed = false;
        $scope.upgradeProcessError = false;
        $scope.upgradeAlreadyLatestVersion = false;
        $scope.upgradeProcessErrorMessage = '';
        $scope.componentsReadyForNext = true;
        $scope.componentsProcessed = false;
        $scope.componentsProcessError = false;
        $scope.showUnstable = false;

        $scope.tryAgainEnabled = function() {
            return ($scope.upgradeProcessed || $scope.upgradeProcessError)
                && ($scope.updateComponents.no ||
                    ($scope.updateComponents.yes && ($scope.componentsProcessed || $scope.componentsProcessError))
                );
        };

        $http.get('index.php/select-version/systemPackage', {'responseType' : 'json'})
            .then(function successCallback(resp) {
                var data = resp.data;

                if (data.responseType != 'error') {
                    $scope.upgradeProcessError = true;

                    angular.forEach(data.packages, function (value, key) {
                        if (!value.current) {
                            return $scope.upgradeProcessError = false;
                        }
                    });

                    if ($scope.upgradeProcessError) {
                        $scope.upgradeProcessErrorMessage = "You're already using the latest version, there's nothing for us to do.";
                        $scope.upgradeAlreadyLatestVersion = true;
                    } else {
                        $scope.selectedOption = [];
                        $scope.versions = [];
                        $scope.data = data;
                        angular.forEach(data.packages, function (value, key) {
                            if (value.stable && !value.current) {
                                $scope.versions.push({
                                    'versionInfo': angular.toJson({
                                        'package': value.package,
                                        'version': value.id
                                    }),
                                    'version': value
                                });
                            } else if (value.stable && value.current) {
                                $scope.currentVersion = value.name;
                            }
                        });

                        if ($scope.versions.length > 0) {
                            $scope.selectedOption = $scope.versions[0].versionInfo;
                            $scope.upgradeReadyForNext = true;
                        }
                    }

                } else {
                    $scope.upgradeProcessError = true;
                    $scope.upgradeProcessErrorMessage = $sce.trustAsHtml(data.error);
                }
                $scope.upgradeProcessed = true;
            }, function errorCallback() {
                $scope.upgradeProcessError = true;
            });

        $scope.updateComponents = {
            yes: false,
            no: true
        };

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

        $scope.$watch('updateComponents.no', function() {
            if (angular.equals($scope.updateComponents.no, true)) {
                $scope.updateComponents.yes = false;
            }
        });

        $scope.$watch('updateComponents.yes', function() {
            if (angular.equals($scope.updateComponents.yes, true)) {
                $scope.updateComponents.no = false;
                if (!$scope.componentsProcessed && !$scope.componentsProcessError) {
                    $scope.componentsReadyForNext = false;
                    $http.get('index.php/other-components-grid/components', {'responseType': 'json'}).
                        then(function successCallback(resp) {
                            var data = resp.data;

                            if (data.responseType != 'error') {
                                $scope.components = data.components;
                                $scope.displayComponents = data.components;
                                $scope.totalForGrid = data.total;
                                $scope.total = data.total;
                                $scope.currentPage = 1;
                                $scope.rowLimit = '20';
                                $scope.numberOfPages = Math.ceil(data.total/$scope.rowLimit);
                                for (var i = 0; i < $scope.totalForGrid; i++) {
                                    $scope.packages.push({
                                        name: $scope.components[i].name,
                                        version: $scope.components[i].updates[0].id
                                    });
                                }
                                $scope.componentsReadyForNext = true;
                            } else {
                                $scope.componentsProcessError = true;
                            }
                            $scope.componentsProcessed = true;
                        }, function errorCallback() {
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

        $scope.AddRemoveComponentOnSliderMove = function(component) {
            var found = false;
            for (var i = 0; i < $scope.packages.length; i++) {
                if ($scope.packages[i].name === component.name) {
                    $scope.packages.splice(i, 1);
                    $scope.totalForGrid = $scope.totalForGrid - 1;
                    found = true;
                }
            }
            if (!found) {
                $scope.packages.push({
                    name: component.name,
                    version: component.dropdownId
                });
                $scope.totalForGrid = $scope.totalForGrid + 1;
            }
        };

        $scope.isSelected = function(name) {
            for (var i = 0; i < $scope.packages.length; i++) {
                if ($scope.packages[i].name === name) {
                    return true;
                }
            }
            return false;
        };

        $scope.showUnstableClick = function() {
            $scope.upgradeReadyForNext = false;
            $scope.selectedOption = [];
            $scope.versions = [];
            angular.forEach($scope.data.packages, function (value, key) {
                if ((value.stable || $scope.showUnstable) && !value.current) {
                    $scope.versions.push({
                        'versionInfo': angular.toJson({
                            'package': value.package,
                            'version': value.id
                        }),
                        'version': value
                    });
                }
            });

            if ($scope.versions.length > 0) {
                $scope.selectedOption = $scope.versions[0].versionInfo;
                $scope.upgradeReadyForNext = true;
            }
        };

        $scope.update = function() {
            var selectedVersionInfo = angular.fromJson($scope.selectedOption);
            $scope.packages[0]['name'] = selectedVersionInfo.package;
            $scope.packages[0].version = selectedVersionInfo.version;
            if (angular.equals($scope.updateComponents.no, true)) {
                if ($scope.totalForGrid > 0) {
                    $scope.packages.splice(1, $scope.totalForGrid);
                }
            }
            $localStorage.moduleName = '';
            $localStorage.packages = $scope.packages;
            $scope.nextState();
        };
    }]);
