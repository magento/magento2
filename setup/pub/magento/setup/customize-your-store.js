/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('customize-your-store', ['ngStorage'])
    .controller('customizeYourStoreController', ['$scope', '$localStorage', function ($scope, $localStorage) {
        $scope.store = {
            timezone: 'America/Los_Angeles',
            currency: 'USD',
            language: 'en_US',
            useSampleData: false
        };

        if ($localStorage.store) {
            $scope.store = $localStorage.store;
        }

        $scope.$on('nextState', function () {
            $localStorage.store = $scope.store;
        });
    }]);
