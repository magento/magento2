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

        $scope.processed = true;

        $http.get('index.php/select-version/systemPackage',{'responseType' : 'json'})
            .success(function (data) {
                $scope.package.name = data.package.package;
                $scope.versions = data.package.versions;
                $scope.processed = false;
            })
            .error(function (data) {
            });

        $scope.update = function(component) {
            console.log($scope.package);
            $localStorage.packages = [
                $scope.package
            ];
            $scope.nextState();
        };

    }]);