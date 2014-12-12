/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
InfiniteScroll = TestCase('InfiniteScroll');
InfiniteScroll.prototype.testInit = function() {
    jQuery(window).infinite_scroll({url: ''});
    assertEquals(true, !!jQuery(window).data('vdeInfinite_scroll'));
    jQuery(window).infinite_scroll('destroy');
};
