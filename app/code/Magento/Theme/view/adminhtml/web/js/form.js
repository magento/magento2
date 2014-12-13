/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define(["prototype"], function(){

function parentThemeOnChange(selected, defaultsById) {
    var statusBar = $$('.tab-item-link')[0];
    var isChanged = statusBar.hasClassName('changed');
    if (!isChanged) {
        var defaults = defaultsById[selected];
        $('theme_title').value = defaults.theme_title;
    }
}

window.parentThemeOnChange = parentThemeOnChange;

});