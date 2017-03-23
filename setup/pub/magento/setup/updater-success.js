/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('updater-success', ['ngStorage'])
    .controller('updaterSuccessController', ['$scope', '$state', '$localStorage', '$window', 'navigationService', function ($scope, $state, $localStorage, $window, navigationService) {
        if ($localStorage.successPageAction) {
            $scope.successPageAction = $localStorage.successPageAction;
            switch (true) {
                case $scope.endsWith($scope.successPageAction, 'd'):
                    $scope.successPageActionMessage = $scope.successPageAction;
                    break;
                case $scope.endsWith($scope.successPageAction, 'e'):
                    $scope.successPageActionMessage = $scope.successPageAction + 'd';
                    break;
                default:
                    $scope.successPageActionMessage = $scope.successPageAction + 'ed';
            }
        }
        if ($localStorage.packages) {
            $scope.packages = $localStorage.packages;
        }
        if (typeof $localStorage.rollbackStarted !== 'undefined') {
            $scope.rollbackStarted = $localStorage.rollbackStarted;
        }
        $scope.back = function () {
            if ($scope.successPageAction) {
                $scope.goToAction($scope.successPageAction);
            } else {
                $window.location.href = '';
            }
        };
        $localStorage.$reset();
        $scope.isHiddenSpinner = false;
        navigationService.load().then(function () {
            $scope.isHiddenSpinner = true;
        });
    }]);
