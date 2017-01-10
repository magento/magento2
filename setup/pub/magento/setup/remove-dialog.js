/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('remove-dialog', [])
    .controller('removeDialogController', ['$rootScope', '$scope', '$localStorage',
        function ($rootScope, $scope, $localStorage) {
            $scope.removeExtension = function (name) {
                delete $scope.componentDependency.packages[name];
                $localStorage.packages = $scope.componentDependency.packages;
                $rootScope.needReCheck = true;
                $scope.closeThisDialog();
            };
        }]);
