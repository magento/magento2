/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('update-extension-grid', ['ngStorage', 'clickOut'])
    .controller('updateExtensionGridController', ['$scope', '$http', '$localStorage', 'titleService', 'authService', 'paginationService', 'multipleChoiceService',
        function ($scope, $http, $localStorage, titleService, authService, paginationService, multipleChoiceService) {
            $scope.isHiddenSpinner = false;

            $http.get('index.php/updateExtensionGrid/extensions').success(function(data) {
                $scope.error = false;
                $scope.errorMessage = '';
                $scope.extensionsVersions = {};
                $scope.multipleChoiceService = multipleChoiceService;
                $scope.multipleChoiceService.reset();
                angular.forEach(data.extensions, function(extension) {
                    extension.updateVersion = extension.latestVersion;
                    $scope.multipleChoiceService.addExtension(extension.name, extension.latestVersion);
                    $scope.extensionsVersions[extension.name] = {
                        'currentVersion': extension.version,
                        'versions': extension.versions
                    };
                });
                $scope.extensions = data.extensions;
                $scope.total = data.total;
                $scope.currentPage = 1;
                $scope.rowLimit = 20;
                $scope.numberOfPages = Math.ceil($scope.total / $scope.rowLimit);
                $scope.isHiddenSpinner = true;
                $localStorage.extensionsVersions = $scope.extensionsVersions;
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
                titleService.setTitle('update', extension);
                $scope.nextState();
            };
            $scope.isHiddenSpinner = true;
            $scope.updateAll = function() {
                $scope.isHiddenSpinner = false;
                authService.checkAuth({
                    success: function(response) {
                        $scope.isHiddenSpinner = true;
                        var result = $scope.multipleChoiceService.checkSelectedExtensions();
                        $scope.error = result.error;
                        $scope.errorMessage = result.errorMessage;

                        if (!$scope.error) {
                            $scope.nextState();
                        }
                    },
                    fail: function(response) {
                        $scope.isHiddenSpinner = true;
                        authService.openAuthDialog($scope);
                    },
                    error: function() {
                        $scope.isHiddenSpinner = true;
                        $scope.error = true;
                        $scope.errorMessage = 'Internal server error';
                    }
                });
            };
        }
    ]);
