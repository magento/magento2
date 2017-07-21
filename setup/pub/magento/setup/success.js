/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('success', ['ngStorage'])
    .controller('successController', ['$scope', '$localStorage', function ($scope, $localStorage) {
        $scope.url = {
            front: '',
            admin: ''
        };
        $scope.db     = $localStorage.db;
        $scope.admin  = $localStorage.admin;
        $scope.config = $localStorage.config;
        if ($scope.config.https.front) {
            $scope.url.front = $scope.config.https.text;
        } else {
            $scope.url.front = $scope.config.address.actual_base_url;
        }
        if ($scope.config.https.admin) {
            $scope.url.admin = $scope.config.https.text + $scope.config.address.admin + '/';
        } else {
            $scope.url.admin = $scope.config.address.actual_base_url + $scope.config.address.admin + '/';
        }
        $scope.messages = $localStorage.messages;
        $localStorage.$reset();
        $scope.admin.password = '';
        $scope.db.password = '';
        $localStorage.$reset();
    }]);
