/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('select-version', ['ngStorage'])
    .controller('selectVersionController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
        $scope.package = {
            name: '',
            version: ''
        };

        $scope.processed = false;
        $scope.processError = false;
        $scope.readyToNext = false;

        $http.get('index.php/select-version/systemPackage',{'responseType' : 'json'})
            .success(function (data) {
                if (data.responseType != 'error') {
                    $scope.package.name = data.package.package;
                    $scope.versions = data.package.versions;
                    $scope.selectedOption = $scope.versions[0].id;

                    $scope.readyToNext = true;
                } else {
                    $scope.processError = true;
                }

                $scope.processed = true;
            })
            .error(function (data) {
            });

        $scope.update = function(component) {
            console.log($scope.package);
            $localStorage.packages = [
                $scope.package
            ];
            //$scope.nextState();
        };

    }]);