<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that My Cart link redirects to the shopping cart page.
 */
class AssertMyCartLinkRedirect extends AbstractConstraint
{
    /**
     * Title of the shopping cart page.
     */
    const CART_PAGE_TITLE = 'Shopping Cart';

    /**
     * Assert that customer is redirected to the shopping cart page after clicking on My Cart link.
     *
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex)
    {
        $cmsIndex->open();
        $cmsIndex->getCartSidebarBlock()->openMiniCart();
        \PHPUnit_Framework_Assert::assertEquals(
            self::CART_PAGE_TITLE,
            $cmsIndex->getTitleBlock()->getTitle(),
            'Wrong page is displayed instead of the shopping cart page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'My Cart link redirects to the shopping cart page.';
    }
}
