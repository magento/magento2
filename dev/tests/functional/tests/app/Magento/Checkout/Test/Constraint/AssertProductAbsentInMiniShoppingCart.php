<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductAbsentInMiniShoppingCart
 * Check that product is absent in mini shopping cart
 */
class AssertProductAbsentInMiniShoppingCart extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert product is absent on mini shopping cart
     *
     * @param CmsIndex $cmsIndex
     * @param FixtureInterface $deletedProduct
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, FixtureInterface $deletedProduct)
    {
        $cmsIndex->open();
        $cmsIndex->getCartSidebarBlock()->openMiniCart();
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getCartSidebarBlock()->getCartItem($deletedProduct)->isVisible(),
            'Product ' . $deletedProduct->getName() . ' is presents in Mini Shopping Cart.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is absent in Mini Shopping Cart.';
    }
}
