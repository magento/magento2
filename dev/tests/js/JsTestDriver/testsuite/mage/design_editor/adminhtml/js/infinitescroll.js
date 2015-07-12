/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
InfiniteScroll = TestCase('InfiniteScroll');
InfiniteScroll.prototype.testInit = function() {
    jQuery(window).infinite_scroll({url: ''});
    assertEquals(true, !!jQuery(window).data('vdeInfinite_scroll'));
    jQuery(window).infinite_scroll('destroy');
};
