/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

'use strict';
angular.module('install', ['ngStorage'])
    .controller('installController', ['$scope', '$sce', '$timeout', '$localStorage', '$rootScope', 'progress', function ($scope, $sce, $timeout, $localStorage, $rootScope, progress) {
        $scope.isStarted = false;
        $scope.isInProgress = false;
        $scope.isConsole = false;
        $scope.isDisabled = false;
        $scope.toggleConsole = function () {
            $scope.isConsole = $scope.isConsole === false;
        };

        $scope.checkProgress = function () {
            $scope.displayProgress();
            progress.get(function (response) {
                var log = '';
                response.data.console.forEach(function (message) {
                    log = log + message + '<br>';
                });
                $scope.log = $sce.trustAsHtml(log);

                if (response.data.success) {
                    $scope.progress = response.data.progress;
                    $scope.progressText = response.data.progress + '%';
                } else {
                    $scope.displayFailure();
                }
                if ($scope.isInProgress) {
                    $timeout(function() {
                        $scope.checkProgress();
                    }, 1500);
                }
            });
        };

        $scope.start = function () {
            var data = {
                'db': $localStorage.db,
                'admin': $localStorage.admin,
                'store': $localStorage.store,
                'config': $localStorage.config
            };
            $scope.isStarted = true;
            $scope.isInProgress = true;
            progress.post(data, function (response) {
                $scope.isInProgress = false;
                if (response.success) {
                    $localStorage.config.encrypt.key = response.key;
                    $scope.nextState();
                } else {
                    $scope.displayFailure();
                }
            });
            progress.get(function () {
                $scope.checkProgress();
            });
        };
        $scope.displayProgress = function() {
            $scope.isFailed = false;
            $scope.isDisabled = true;
            $rootScope.isMenuEnabled = false;
        };
        $scope.displayFailure = function () {
            $scope.isFailed = true;
            $scope.isDisabled = false;
            $rootScope.isMenuEnabled = true;
        };
    }])
    .service('progress', ['$http', function ($http) {
        return {
            get: function (callback) {
                $http.get('install/progress').then(callback);
            },
            post: function (data, callback) {
                $http.post('install/start', data).success(callback);
            }
        };
    }]);
