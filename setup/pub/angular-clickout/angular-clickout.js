/*! Angular clickout v1.0.2 | © 2014 Greg Bergé | License MIT */
(function (window, angular, undefined) { 'use strict';

  /**
   * Click out directive.
   * Execute an angular expression when we click out of the current element.
   */

  angular.module('clickOut', [])
  .directive('clickOut', ['$window', '$parse', function ($window, $parse) {
    return {
      restrict: 'A',
      link: function (scope, element, attrs) {
        var clickOutHandler = $parse(attrs.clickOut);

        angular.element($window).on('click', function (event) {
          if (element[0].contains(event.target)) return;
          clickOutHandler(scope, {$event: event});
          scope.$apply();
        });
      }
    };
  }]);

}(window, window.angular));
