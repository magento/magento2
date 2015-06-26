/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-upgrade-success', ['ngStorage'])
    .controller('componentUpgradeSuccessController', ['$scope', '$state', '$localStorage', '$window', function ($scope, $state, $localStorage, $window) {
        if ($localStorage.package) {
            $scope.package = $localStorage.package;
        }
        $scope.back = function () {
            $window.location.href = '';
        }
    }]);
