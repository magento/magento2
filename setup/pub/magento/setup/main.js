/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
var main = angular.module('main', ['ngStorage']);
main.controller('navigationController', ['$scope', '$state', '$rootScope', 'navigationService', function ($scope, $state, $rootScope, navigationService) {
    navigationService.load();
    $rootScope.isMenuEnabled = true;
    $scope.itemStatus = function (order) {
        return $state.$current.order <= order || !$rootScope.isMenuEnabled;
    };
}])
.controller('mainController', [
    '$scope', '$state', 'navigationService',
    function ($scope, $state, navigationService) {
        $scope.$on('$stateChangeSuccess', function (event, state) {
            $scope.valid = true;
            $scope.class = 'col-lg-9';
            if (state.main) {
                $scope.class = 'col-lg-offset-3 col-lg-6';
            }
        });

        $scope.nextState = function () {
            if ($scope.validate()) {
                $scope.$broadcast('nextState', $state.$current);
                $state.go(navigationService.getNextState().id);
            }
        };

        $scope.previousState = function () {
                $scope.valid = true;
                $state.go(navigationService.getPreviousState().id);
        };

        // Flag indicating the validity of the form
        $scope.valid = true;

        // Check the validity of the form
        $scope.validate = function() {
            if ($state.current.validate) {
                $scope.$broadcast('validate-' + $state.current.id);
            }
            return $scope.valid;
        };

        // Listens on 'validation-response' event, dispatched by descendant controller
        $scope.$on('validation-response', function(event, data) {
            $scope.valid = data;
            event.stopPropagation();
        });
    }
])
.service('navigationService', ['$location', '$state', '$http', function ($location, $state, $http) {
    return {
        mainState: {},
        states: [],
        load: function () {
            var self = this;
            $http.get('index.php/navigation').success(function (data) {
                var currentState = $location.path().replace('/', '');
                var isCurrentStateFound = false;
                self.states = data.nav;
                data.nav.forEach(function (item) {
                    app.stateProvider.state(item.id, item);
                    if (item.default) {
                        self.mainState = item;
                    }

                    if (currentState == item.url) {
                        $state.go(item.id);
                        isCurrentStateFound = true;
                    }
                });
                if (!isCurrentStateFound) {
                    $state.go(self.mainState.id);
                }
            });
        },
        getNextState: function () {
            var nItem = {};
            this.states.forEach(function (item) {
                if (item.order == $state.$current.order + 1) {
                    nItem = item;
                }
            });
            return nItem;
        },
        getPreviousState: function () {
            var nItem = {};
            this.states.forEach(function (item) {
                if (item.order == $state.$current.order - 1) {
                    nItem = item;
                }
            });
            return nItem;
        }
    }
}]);
