// Import every plugin under the sun. Bad for performance,
// but prevents the store from breaking in situations
// where a dependency was missed during the migration from
// a monolith build of jQueryUI to a modular one

define([
    'jquery-ui-modules/core',
    'jquery-ui-modules/widget'
], function() {
    console.warn(
        'Fallback to JQueryUI Compat activated. ' +
        'Your store is missing a dependency for a ' +
        'jQueryUI widget. Identifying and addressing the dependency ' +
        'will drastically improve the performance of your site.'
    );
});
