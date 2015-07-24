/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('system-upgrade', ['ngStorage'])
    .controller('systemUpgradeController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
        $scope.page_title = "hello";
    }]);