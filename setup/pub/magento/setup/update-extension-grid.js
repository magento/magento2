/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('update-extension-grid', ['ngStorage', 'clickOut'])
    .controller('updateExtensionGridController', ['$scope', '$http', '$localStorage', 'titleService',
        function ($scope, $http, $localStorage, titleService) {
            $scope.isHiddenSpinner = false;

            $http.get('index.php/updateExtensionGrid/extensions').success(function(data) {
                $scope.error = false;
                $scope.errorMessage = '';
                angular.forEach(data.extensions, function(extension) {
                    extension.updateVersion = extension.latestVersion;
                });
                $scope.extensions = data.extensions;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.start = 0;
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
                $scope.isHiddenSpinner = true;
            });

            $scope.recalculatePagination = function(currentPage, rowLimit) {
                $scope.currentPage = parseInt(currentPage, 10);
                $scope.rowLimit = parseInt(rowLimit, 10);
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
                if ($scope.currentPage > $scope.numberOfPages) {
                    $scope.currentPage = $scope.numberOfPages;
                }
                $scope.start = ($scope.currentPage - 1) * $scope.rowLimit;
            };

            $scope.predicate = 'name';
            $scope.reverse = false;
            $scope.order = function(predicate) {
                $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
                $scope.predicate = predicate;
            };

            $scope.update = function(extension) {
                $localStorage.packages = [
                    {
                        name: extension.name,
                        version: extension.updateVersion
                    }
                ];
                titleService.setTitle('update', extension.name);
                $scope.nextState();
            };
        }]);
