/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('home', ['ngStorage'])
    .controller('homeController', ['$scope', '$http', '$localStorage', function ($scope, $http, $localStorage) {
         $scope.page_title = "Magento setup tool";
    }]);
