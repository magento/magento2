/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

'use strict';
angular.module('landing', ['ngStorage'])
    .controller('landingController', [
        '$scope',
        '$location',
        '$localStorage',
        function ($scope, $location, $localStorage) {
            $scope.selectLanguage = function () {
                $localStorage.lang = $scope.modelLanguage;
                window.location = 'index.php/' + $scope.modelLanguage + '/index';
            };
        }
    ]);
