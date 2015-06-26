/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-upgrade', ['ngStorage'])
    .controller('componentUpgradeController', ['$scope', '$state', '$localStorage', '$http', '$window', function ($scope, $state, $localStorage, $http, $window) {
        // TODO: hardcode it right now
        $localStorage.packages = [
            {name: 'symfony/console', version: '2.5'}
        ];
        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        $scope.started = false;
        $scope.errorMessage = '';
        $scope.upgrade = function() {
            $scope.started = true;
            $http.post('index.php/component-upgrade/update', $scope.packages)
                .success(function (data) {
                    if (data['success']) {
                        $window.location.href = '../update/index.php';
                    } else {
                        $scope.errorMessage = data['message'];
                    }
                })
                .error(function (data) {
                    $scope.errorMessage = 'Something went wrong. Please try again.';
                });
        }
    }]);
