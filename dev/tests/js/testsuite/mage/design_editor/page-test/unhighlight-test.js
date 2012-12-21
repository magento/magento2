/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    mage.design_editor
 * @package     test
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
PageTestUnhighlight = TestCase('DesignEditor_PageTest_Unhighlight');
PageTestUnhighlight.prototype.testUnhighlight = function() {
    /*:DOC += <iframe name="vde_container_frame" id="vde_container_frame" class="vde_container_frame"></iframe> */
    /*:DOC iframeContent =
        <div>
            <div id="vde_element_1" class="vde_element_wrapper vde_container">
                <div class="vde_element_title">Title 1</div>
                <div id="vde_element_2" class="vde_element_wrapper vde_draggable">
                    <div class="vde_element_title">Title 2</div>
                    <div class="block block-list block-compare" id="block">
                        <div class="block-title">
                            <strong><span>Block Title</span></strong>
                        </div>
                        <div class="block-content">
                            <p class="empty">Block Content</p>
                        </div>
                    </div>
                </div>
            </div>
         </div>
    */

    jQuery.fx.off = true;
    var page = jQuery(window).vde_page();

    var frameSelector = page.vde_page('option', 'frameSelector');

    jQuery(frameSelector).triggerHandler('load');
    jQuery(frameSelector).contents().find("body:first").html(this.iframeContent);

    var highlightElementTitleSelector = page.vde_page('option', 'highlightElementTitleSelector');
    var highlightElementSelector = page.vde_page('option', 'highlightElementSelector');
    assertEquals(true, jQuery(frameSelector).contents().find(highlightElementSelector).size() > 0);
    var hierarchy = {};
    jQuery(frameSelector).contents().find(highlightElementSelector).each(function() {
        var elem = jQuery(this);
        hierarchy[elem.attr('id')] = elem.contents(':not(' + highlightElementTitleSelector + ')');
    });
    page.vde_page('destroy');
    page = jQuery(window).vde_page();
    jQuery(frameSelector).triggerHandler('load');
    page.trigger('unchecked.vde_checkbox');
    var hierarchyIsCorrect = null;
    jQuery.each(hierarchy, function(parentKey, parentVal) {
        jQuery.each(parentVal, function() {
            hierarchyIsCorrect = !jQuery(this).parents('#' + parentKey).size();
        })
    });
    assertEquals(true, hierarchyIsCorrect);

    assertEquals(true, jQuery(frameSelector).contents().find(highlightElementSelector).size() > 0);
    jQuery(frameSelector).contents().find(highlightElementSelector).each(function() {
        assertEquals(false, $(this).is(':visible'));
    });

    assertEquals(true,
        jQuery(frameSelector).contents().find(highlightElementTitleSelector).size() > 0
    );
    jQuery(frameSelector).contents().find(highlightElementTitleSelector).each(function() {
        assertEquals(false, $(this).is(':visible'));
    });

    page.vde_page('destroy');
    jQuery.fx.off = false;
};
