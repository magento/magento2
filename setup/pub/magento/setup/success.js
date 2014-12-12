/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
