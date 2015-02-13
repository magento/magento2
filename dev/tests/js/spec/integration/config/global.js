require.config({
    bundles: {
        'mage/requirejs/static': [
            'jsbuild'
        ]
    },
    config: {
        jsbuild: {
            "dev/tests/js/spec/assets/jsbuild/local.js": "define([], function(){ 'use strict'; return true; });"
        }
    },
    deps: [
        'mage/requirejs/static'
    ],
    paths: {
        'jquery/ui': 'jquery/jquery-ui'
    }
});
