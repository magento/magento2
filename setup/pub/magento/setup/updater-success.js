/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('updater-success', ['ngStorage'])
    .controller('updaterSuccessController', ['$scope', '$state', '$localStorage', '$window', function ($scope, $state, $localStorage, $window) {
        if ($localStorage.successPageAction) {
            $scope.successPageAction = $localStorage.successPageAction;
        }
        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        if (typeof $localStorage.rollbackStarted !== 'undefined') {
            $scope.rollbackStarted = $localStorage.rollbackStarted;
        }
        $scope.back = function () {
            $window.location.href = '';
        }
        $localStorage.$reset();
    }]);
