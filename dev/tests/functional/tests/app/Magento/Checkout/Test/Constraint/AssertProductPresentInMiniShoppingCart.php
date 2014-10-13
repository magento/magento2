<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertProductPresentInMiniShoppingCart
 * Check that product is present in mini shopping cart
 */
class AssertProductPresentInMiniShoppingCart extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert product is present on mini shopping cart
     *
     * @param CmsIndex $cmsIndex
     * @param array $products
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex, array $products)
    {
        $cmsIndex->open();
        foreach ($products as $product) {
            $cmsIndex->getCartSidebarBlock()->openMiniCart();
            \PHPUnit_Framework_Assert::assertTrue(
                $cmsIndex->getCartSidebarBlock()->getCartItem($product)->isVisible(),
                'Product ' . $product->getName() . ' is absent in Mini Shopping Cart.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products is presents in Mini Shopping Cart.';
    }
}
