/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
angular.module('readiness-check', [])
    .constant('COUNTER', 1)
    .controller('readinessCheckController', ['$rootScope', '$scope', '$localStorage', '$http', '$timeout', '$sce', '$state', 'COUNTER', function ($rootScope, $scope, $localStorage, $http, $timeout, $sce, $state, COUNTER) {
        $scope.Object = Object;
        $scope.titles = $localStorage.titles;
        $scope.moduleName = $localStorage.moduleName;
        $scope.progressCounter = COUNTER;
        $scope.startProgress = function() {
            ++$scope.progressCounter;
        };
        $scope.componentDependency = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false,
            errorMessage: '',
            packages: null
        };
        switch ($state.current.type) {
            case 'uninstall':
                $scope.dependencyUrl = 'index.php/dependency-check/uninstall-dependency-check';
                if ($localStorage.packages) {
                    $scope.componentDependency.packages = $localStorage.packages;
                }
                break;
            case 'enable':
            case 'disable':
                $scope.dependencyUrl = 'index.php/dependency-check/enable-disable-dependency-check';
                if ($localStorage.packages) {
                    $scope.componentDependency.packages = {
                        type: $state.current.type,
                        packages: $localStorage.packages
                    };
                }
                break;
            default:
                $scope.dependencyUrl = 'index.php/dependency-check/component-dependency';
                if ($localStorage.packages) {
                    $scope.componentDependency.packages = $localStorage.packages;
                }
        }
        $scope.stopProgress = function() {
            --$scope.progressCounter;
            if ($scope.progressCounter == COUNTER) {
                $scope.resetProgress();
            }
        };
        $scope.resetProgress = function() {
            $scope.progressCounter = 0;
        };
        $rootScope.checkingInProgress = function() {
            return $scope.progressCounter > 0;
        };
        $scope.requestFailedHandler = function(obj) {
            obj.processed = true;
            obj.isRequestError = true;
            $scope.hasErrors = true;
            $rootScope.hasErrors = true;
            $scope.stopProgress();
        };
        $scope.completed = false;
        $scope.hasErrors = false;

        $scope.version = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.settings = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false,
            isRequestWarning: false
        };
        $scope.extensions = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.permissions = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.updater = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false
        };
        $scope.cronScript = {
            visible: false,
            processed: false,
            expanded: false,
            isRequestError: false,
            notice: false,
            setupErrorMessage: '',
            updaterErrorMessage: '',
            setupNoticeMessage: '',
            updaterNoticeMessage: ''
        };
        $scope.items = {
            'php-version': {
                url:'index.php/environment/php-version',
                params: $scope.actionFrom,
                useGet: true,
                show: function() {
                    $scope.startProgress();
                    $scope.version.visible = true;
                },
                process: function(data) {
                    $scope.version.processed = true;
                    angular.extend($scope.version, data);
                    $scope.updateOnProcessed($scope.version.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.version);
                }
            },
            'php-settings': {
                url:'index.php/environment/php-settings',
                params: $scope.actionFrom,
                useGet: true,
                show: function() {
                    $scope.startProgress();
                    $scope.settings.visible = true;
                },
                process: function(data) {
                    $scope.settings.processed = true;
                    angular.extend($scope.settings, data);
                    $scope.updateOnProcessed($scope.settings.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.settings);
                }
            },
            'php-extensions': {
                url:'index.php/environment/php-extensions',
                params: $scope.actionFrom,
                useGet: true,
                show: function() {
                    $scope.startProgress();
                    $scope.extensions.visible = true;
                },
                process: function(data) {
                    $scope.extensions.processed = true;
                    angular.extend($scope.extensions, data);
                    if(data.responseType !== 'error') {
                        $scope.extensions.length = Object.keys($scope.extensions.data.required).length;
                    }
                    $scope.updateOnProcessed($scope.extensions.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.extensions);
                }
            }
        };

        if ($scope.actionFrom === 'installer') {
            $scope.items['file-permissions'] = {
                url:'index.php/environment/file-permissions',
                show: function() {
                    $scope.startProgress();
                    $scope.permissions.visible = true;
                },
                process: function(data) {
                    $scope.permissions.processed = true;
                    angular.extend($scope.permissions, data);
                    $scope.updateOnProcessed($scope.permissions.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.permissions);
                }
            };
        }

        if ($scope.actionFrom === 'updater') {
            $scope.items['updater-application'] = {
                url:'index.php/environment/updater-application',
                show: function() {
                    $scope.startProgress();
                    $scope.updater.visible = true;
                },
                process: function(data) {
                    $scope.updater.processed = true;
                    angular.extend($scope.updater, data);
                    $scope.updateOnProcessed($scope.updater.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.updater);
                }
            };
            $scope.items['cron-script'] = {
                url:'index.php/environment/cron-script',
                show: function() {
                    $scope.startProgress();
                    $scope.cronScript.visible = true;
                },
                process: function(data) {
                    $scope.cronScript.processed = true;
                    if (data.setupErrorMessage) {
                        data.setupErrorMessage = $sce.trustAsHtml(data.setupErrorMessage);
                    }
                    if (data.updaterErrorMessage) {
                        data.updaterErrorMessage = $sce.trustAsHtml(data.updaterErrorMessage);
                    }
                    if (data.setupNoticeMessage) {
                        $scope.cronScript.notice = true;
                        data.setupNoticeMessage = $sce.trustAsHtml(data.setupNoticeMessage);
                    }
                    if (data.updaterNoticeMessage) {
                        $scope.cronScript.notice = true;
                        data.updaterNoticeMessage = $sce.trustAsHtml(data.updaterNoticeMessage);
                    }
                    angular.extend($scope.cronScript, data);
                    $scope.updateOnProcessed($scope.cronScript.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.cronScript);
                }
            };
            $scope.items['component-dependency'] = {
                url: $scope.dependencyUrl,
                params: $scope.componentDependency.packages,
                show: function() {
                    $scope.startProgress();
                    $scope.componentDependency.visible = true;
                },
                process: function(data) {
                    $scope.componentDependency.processed = true;
                    if (data.errorMessage) {
                        data.errorMessage = $sce.trustAsHtml(data.errorMessage);
                    }
                    angular.extend($scope.componentDependency, data);
                    $scope.updateOnProcessed($scope.componentDependency.responseType);
                    $scope.stopProgress();
                },
                fail: function() {
                    $scope.requestFailedHandler($scope.componentDependency);
                }
            };
        }

        $scope.isCompleted = function() {
            return $scope.version.processed
                && $scope.settings.processed
                && $scope.extensions.processed
                && ($scope.permissions.processed || ($scope.actionFrom === 'updater'))
                && (($scope.cronScript.processed && $scope.componentDependency.processed && $scope.updater.processed)
                || ($scope.actionFrom !== 'updater'));
        };

        $scope.updateOnProcessed = function(value) {
            if (!$rootScope.hasErrors) {
                $rootScope.hasErrors = (value != 'success');
                $scope.hasErrors = $rootScope.hasErrors;
            }
        };

        $scope.updateOnError = function(obj) {
            obj.expanded = true;
        };

        $scope.updateOnSuccess = function(obj) {
            obj.expanded = false;
        };

        $scope.updateOnExpand = function(obj) {
            obj.expanded = !obj.expanded;
        };

        $scope.hasItem = function(haystack, needle) {
            return haystack.indexOf(needle) > -1;
        };

        $scope.query = function(item) {
            if (item.params) {
                if (item.useGet === true) {
                    // The http request type has been changed from POST to GET for a reason. The POST request
                    // results in PHP throwing a warning regards to 'always_populate_raw_post_data'
                    // being incorrectly set to a value different than -1. This warning is throw during the initial
                    // boot up sequence when POST request is received before the control gets transferred over to
                    // the Magento customer error handler, hence not catchable. To avoid that warning, the HTTP
                    // request type is being changed from POST to GET for select queries. Those queries are:
                    // (1) PHP Version Check (2) PHP Settings Check and (3) PHP Extensions Check.

                    item.url = item.url + '?type=' + item.params;
                } else {
                    return $http.post(item.url, item.params)
                        .success(function (data) {
                            item.process(data)
                        })
                        .error(function (data, status) {
                            item.fail();
                        });
                }
            }
            // setting 1 minute timeout to prevent system from timing out
            return $http.get(item.url, {timeout: 60000})
                .success(function(data) { item.process(data) })
                .error(function(data, status) {
                    item.fail();
                });
        };

        $scope.progress = function() {
            $rootScope.hasErrors = false;
            $scope.hasErrors = false;
            angular.forEach($scope.items, function(item) {
                item.show();
            });
            var $delay = 0;
            angular.forEach($scope.items, function(item) {
                $timeout(function() { $scope.query(item); }, $delay * 1000);
                $delay++;
            });
        };

        $scope.$on('$stateChangeSuccess', function (event, nextState) {
            if (nextState.id == 'root.readiness-check-' + nextState.type +'.progress') {
                $scope.progress();
            }
        });

        $scope.wordingOfReadinessCheckAction = function() {
            var $actionString = 'We\'re making sure your server environment is ready for ';
            if ($localStorage.moduleName) {
                $actionString += $localStorage.moduleName;
            } else {
                if($state.current.type === 'install' || $state.current.type === 'upgrade') {
                    $actionString += 'Magento';
                    if ($state.current.type === 'upgrade' && $localStorage.packages.length > 1 ) {
                        $actionString += ' and selected components';
                    }
                } else {
                    $actionString += 'package';
                }
            }
            $actionString += " to be " + $state.current.type;
            if ($scope.endsWith($state.current.type, 'e')) {
                $actionString += 'd';
            } else {
                $actionString +='ed';
            }
            return $actionString;
        }
    }]);
