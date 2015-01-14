/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('success', ['ngStorage'])
    .controller('successController', ['$scope', '$localStorage', function ($scope, $localStorage) {
        $scope.db     = $localStorage.db;
        $scope.admin  = $localStorage.admin;
        $scope.config = $localStorage.config;
        $scope.messages = $localStorage.messages;
        $localStorage.$reset();
    }]);
