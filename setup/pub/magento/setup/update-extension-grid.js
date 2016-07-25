/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('update-extension-grid', ['ngStorage', 'clickOut'])
    .controller('updateExtensionGridController', ['$scope', '$http', '$localStorage', 'titleService', 'paginationService',
        function ($scope, $http, $localStorage, titleService, paginationService) {
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
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
                $scope.isHiddenSpinner = true;
            });

            paginationService.initWatchers($scope);

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
        }
    ]);
