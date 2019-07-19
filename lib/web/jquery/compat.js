// Import every plugin under the sun. Bad for performance,
// but prevents the store from breaking in situations
// where a dependency was missed during the migration from
// a monolith build of jQueryUI to a modular one

define([
    'jquery-ui-modules/core',
    'jquery-ui-modules/accordion',
    'jquery-ui-modules/autocomplete',
    'jquery-ui-modules/button',
    'jquery-ui-modules/datepicker',
    'jquery-ui-modules/dialog',
    'jquery-ui-modules/draggable',
    'jquery-ui-modules/droppable',
    'jquery-ui-modules/effect-blind',
    'jquery-ui-modules/effect-bounce',
    'jquery-ui-modules/effect-clip',
    'jquery-ui-modules/effect-drop',
    'jquery-ui-modules/effect-explode',
    'jquery-ui-modules/effect-fade',
    'jquery-ui-modules/effect-fold',
    'jquery-ui-modules/effect-highlight',
    'jquery-ui-modules/effect-scale',
    'jquery-ui-modules/effect-pulsate',
    'jquery-ui-modules/effect-shake',
    'jquery-ui-modules/effect-slide',
    'jquery-ui-modules/effect-transfer',
    'jquery-ui-modules/effect',
    'jquery-ui-modules/menu',
    'jquery-ui-modules/mouse',
    'jquery-ui-modules/position',
    'jquery-ui-modules/progressbar',
    'jquery-ui-modules/resizable',
    'jquery-ui-modules/selectable',
    'jquery-ui-modules/slider',
    'jquery-ui-modules/sortable',
    'jquery-ui-modules/spinner',
    'jquery-ui-modules/tabs',
    'jquery-ui-modules/timepicker',
    'jquery-ui-modules/tooltip',
    'jquery-ui-modules/widget'
], function() {
    console.warn(
        'Fallback to JQueryUI Compat activated. ' +
        'Your store is missing a dependency for a ' +
        'jQueryUI widget. Identifying and addressing the dependency ' +
        'will drastically improve the performance of your site.'
    )
});