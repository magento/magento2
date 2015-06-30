/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-grid', ['ngStorage'])
    .controller('componentGridController', ['$scope', '$http', function ($scope, $http) {
      $http.get('index.php/componentGrid/components').success(function(data) {
        $scope.components = data.components;
      });
    }]);
