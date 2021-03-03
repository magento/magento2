/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
var main = angular.module('main', ['ngStorage', 'ngDialog']);
main.controller('navigationController',
        ['$scope', '$state', '$rootScope', '$window', 'navigationService', '$localStorage',
            function ($scope, $state, $rootScope, $window, navigationService, $localStorage) {

    function loadMenu() {
        angular.element(document).ready(function() {
            $scope.menu = $localStorage.menu;
        });
    }

    navigationService.load().then(loadMenu);

    $rootScope.isMenuEnabled = true;
    $scope.itemStatus = function (order) {
        return $state.$current.order <= order || !$rootScope.isMenuEnabled;
    };
}])
.controller('mainController', [
    '$scope', '$state', 'navigationService', '$localStorage',
    function ($scope, $state, navigationService, $localStorage) {

        $scope.moduleName = $localStorage.moduleName;

        $scope.nextState = function () {
            $scope.$broadcast('nextState', $state.$current);
            $state.go(navigationService.getNextState().id);
        };

        $scope.state = $state;

        $scope.previousState = function () {
                $state.go(navigationService.getPreviousState().id);
        };
    }
])
.service('navigationService', ['$location', '$state', '$http', '$localStorage',
    function ($location, $state, $http, $localStorage) {
    return {
        mainState: {},
        states: [],
        titlesWithModuleName: ['enable', 'disable', 'update', 'uninstall'],
        isLoadedStates: false,
        load: function () {
            var self = this;

            return $http.get('index.php/navigation').then(function successCallback(resp) {
                var data = resp.data,
                    currentState = $location.path().replace('/', ''),
                    isCurrentStateFound = false;

                self.states = data.nav;
                $localStorage.menu = data.menu;
                self.titlesWithModuleName.forEach(function (value) {
                    data.titles[value] = data.titles[value] + $localStorage.moduleName;
                });
                $localStorage.titles = data.titles;
                if (self.isLoadedStates == false) {
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
                    self.isLoadedStates = true;
                }
            });
        },
        getNextState: function () {
            var nItem = {};
            this.states.forEach(function (item) {
                if (item.order == $state.$current.order + 1 && item.type == $state.$current.type) {
                    nItem = item;
                }
            });
            return nItem;
        },
        getPreviousState: function () {
            var nItem = {};
            this.states.forEach(function (item) {
                if (item.order == $state.$current.order - 1 && item.type == $state.$current.type) {
                    nItem = item;
                }
            });
            return nItem;
        }
    };
}])
.filter('startFrom', function () {
    return function (input, start) {
        if (input !== undefined && start !== 'NaN') {
            start = parseInt(start, 10);
            return input.slice(start);
        }
        return 0;
    };
});
