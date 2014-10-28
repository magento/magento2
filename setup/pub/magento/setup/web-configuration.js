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
angular.module('web-configuration', ['ngStorage'])
    .controller('webConfigurationController', ['$scope', '$state', '$localStorage', function ($scope, $state, $localStorage) {
        $scope.config = {
            address: {
                web: 'http://www.example.com/',
                admin: 'admin'
            },
            https: {
                front: true,
                admin: true
            },
            rewrites: {
                allowed: true
            },
            encrypt: {
                key: null,
                type: 'magento'
            },
            advanced: {
                expanded: false
            }
        };

        if ($localStorage.config) {
            $scope.config = $localStorage.config;
        }

        $scope.$on('nextState', function () {
            $localStorage.config = $scope.config;
        });

        $scope.updateOnExpand = function(obj) {
            obj.expanded = !obj.expanded;
        }

        $scope.$watch('config.encrypt.type', function() {
            if(angular.equals($scope.config.encrypt.type, 'magento')){
                $scope.config.encrypt.key = null;
            }
        });

        $scope.showEncryptKey = function() {
            return angular.equals($scope.config.encrypt.type, 'user');
        }

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.webconfig.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.webconfig.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.webconfig.$valid }, function(valid) {
            if (valid) {
                $scope.webconfig.submitted = false;
            }
        });
    }]);