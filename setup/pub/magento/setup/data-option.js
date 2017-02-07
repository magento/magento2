/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('data-option', ['ngStorage'])
    .controller('dataOptionController', ['$scope', '$localStorage', '$http', function ($scope, $localStorage, $http) {
        $scope.component = {
            dataOption : false,
            hasUninstall : false
        };

        if ($localStorage.componentType === 'magento2-module') {
            $http.post('index.php/data-option/hasUninstall', {'moduleName' : $localStorage.moduleName})
                .success(function(data) {
                    $scope.component.hasUninstall = data.hasUninstall;
            });
        }

        if ($localStorage.dataOption) {
            $scope.component.dataOption = $localStorage.dataOption;
        }

        $scope.$watch('component.dataOption', function(value) {
            $localStorage.dataOption = value;
        });
    }]);
