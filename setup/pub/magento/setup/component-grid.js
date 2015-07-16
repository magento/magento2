/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('component-grid', ['ngStorage'])
    .controller('componentGridController', ['$scope', '$http', function ($scope, $http) {

      $http.get('index.php/componentGrid/components').success(function(data) {
          $scope.components = data.components;
          $scope.total = data.total;
          if(typeof data.lastSyncData.lastSyncDate === "undefined") {
              $scope.isOutOfSync = true;
          } else {
              $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
              $scope.isOutOfSync = false;
          }
          $scope.availableUpdatePackages = data.lastSyncData.packages;

      });

      $scope.isOutOfSync = false;
      $scope.isHiddenSpinner = true;
      $scope.selectedComponent = null;

      $scope.isActiveActionsCell = function(component) {
          return $scope.selectedComponent === component;
      };

      $scope.toggleActiveActionsCell = function(component) {
          $scope.selectedComponent = $scope.selectedComponent == component ? null : component;
      };

      $scope.sync = function() {
          $scope.isHiddenSpinner = false;
          $http.get('index.php/componentGrid/sync').success(function(data) {
              $scope.lastSyncDate = $scope.convertDate(data.lastSyncData.lastSyncDate);
              $scope.availableUpdatePackages = data.lastSyncData.packages;
              $scope.isHiddenSpinner = true;
              $scope.isOutOfSync = false;
          });
      };

      $scope.isAvailableUpdatePackage = function(packageName) {
          return packageName in $scope.availableUpdatePackages;
      };

      $scope.getIndicatorInfo = function(component, type) {
          var indicators = {
              'info' : {'icon' : '_info', 'label' : 'Update Available'},
              'on' : {'icon' : '_on', 'label' : 'On'},
              'off' : {'icon' : '_off', 'label' : 'Off'}
          };

          var types = ['label', 'icon'];

          if (types.indexOf(type) == -1) {
            type = 'icon';
          }

          if (component.name in $scope.availableUpdatePackages) {
              return indicators.info[type];
          }
          return indicators.on[type];
      };

      $scope.convertDate = function(date) {
          return new Date(date);
      }
    }]);
