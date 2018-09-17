/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';
var app = angular.module(
    'magentoSetup',
    [
        'ui.router',
        'ui.bootstrap',
        'main',
        'landing',
        'readiness-check',
        'add-database',
        'web-configuration',
        'customize-your-store',
        'create-admin-account',
        'install',
        'success',
        'component-grid',
        'install-extension-grid',
        'create-backup',
        'complete-backup',
        'data-option',
        'start-updater',
        'select-version',
        'updater-success',
        'home',
        'auth-dialog',
        'system-config',
        'marketplace-credentials'
    ]);

app.config(['$httpProvider', '$stateProvider', function ($httpProvider, $stateProvider) {
    if (!$httpProvider.defaults.headers.get) {
        $httpProvider.defaults.headers.get = {};
    }
    $httpProvider.defaults.headers.get['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    $httpProvider.defaults.headers.get['Pragma'] = 'no-cache';
    $httpProvider.defaults.headers.get['Expires'] = 0;
    app.stateProvider = $stateProvider;
}])
    .config(function($provide) {
        $provide.decorator('$state', function($delegate, $stateParams) {
            $delegate.forceReload = function() {
                return $delegate.go($delegate.current, $stateParams, {
                    reload: true,
                    inherit: false,
                    notify: true
                });
            };
            return $delegate;
        });
    })
    .run(function ($rootScope, $state) {
        $rootScope.$state = $state;
    });
