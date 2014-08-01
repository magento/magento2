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
angular.module('create-admin-account', ['ngStorage'])
    .controller('createAdminAccountController', ['$scope', '$state', '$localStorage', function ($scope, $state, $localStorage) {
        $scope.admin = {
            'passwordStatus': {
                class: 'none',
                label: 'None'
            }
        };

        $scope.passwordStatusChange = function () {
            if (angular.isUndefined($scope.admin.password)) {
                return;
            }
            var p = $scope.admin.password;
            if (p.length > 6 && p.match(/[\d]+/) && p.match(/[a-z]+/) && p.match(/[A-Z]+/) && p.match(/[!@#$%^*()_\/\\\-\+=]+/)) {
                $scope.admin.passwordStatus.class = 'strong';
                $scope.admin.passwordStatus.label = 'Strong';
            } else if (p.length > 6 && p.match(/[\d]+/) && p.match(/[a-z]+/) && p.match(/[A-Z]+/)) {
                $scope.admin.passwordStatus.class = 'good';
                $scope.admin.passwordStatus.label = 'Good';
            } else if (p.length > 6 && p.match(/[\d]+/) && p.match(/[a-zA-Z]+/)) {
                $scope.admin.passwordStatus.class = 'weak';
                $scope.admin.passwordStatus.label = 'Weak';
            } else if (p.length > 6) {
                $scope.admin.passwordStatus.class = 'to-short';
                $scope.admin.passwordStatus.label = 'To Short';
            } else {
                $scope.admin.passwordStatus.class = 'none';
                $scope.admin.passwordStatus.label = 'None';
            }
        };

        if ($localStorage.admin) {
            $scope.admin = $localStorage.admin;
        }

        $scope.$on('nextState', function () {
            $localStorage.admin = $scope.admin;
        });

        // Listens on form validate event, dispatched by parent controller
        $scope.$on('validate-' + $state.current.id, function() {
            $scope.validate();
        });

        // Dispatch 'validation-response' event to parent controller
        $scope.validate = function() {
            if ($scope.account.$valid) {
                $scope.$emit('validation-response', true);
            } else {
                $scope.$emit('validation-response', false);
                $scope.account.submitted = true;
            }
        }

        // Update 'submitted' flag
        $scope.$watch(function() { return $scope.account.$valid }, function(valid) {
            if (valid) {
                $scope.account.submitted = false;
            }
        });
    }])
    .directive('confirmPassword', function() {
        return {
            require: 'ngModel',
            restrict: 'A',
            link: function (scope, elem, attrs, ctrl) {
                scope.$watch(function () {
                    return scope.$eval(attrs.confirmPassword) === ctrl.$modelValue;
                }, function (value) {
                    ctrl.$setValidity('confirmPassword', value);
                });

                ctrl.$parsers.push(function (value) {
                    if (angular.isUndefined(value) || value === '') {
                        ctrl.$setValidity('confirmPassword', true);
                        return value;
                    }
                    var validated = value === scope.confirmPassword;
                    ctrl.$setValidity('confirmPassword', validated);
                    return value;
                });
            }
        };
    });
