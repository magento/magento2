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

namespace Magento\Wishlist\Test\Constraint;

use Mtf\Fixture\InjectableFixture;
use Magento\Cms\Test\Page\CmsIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Wishlist\Test\Page\WishlistIndex;

/**
 * Class AssertWishlistIsEmpty
 * Check that there are no Products in Wishlist
 */
class AssertWishlistIsEmpty extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Check that there are no Products in Wishlist
     *
     * @param InjectableFixture[] $products
     * @param CmsIndex $cmsIndex
     * @param WishlistIndex $wishlistIndex
     * @return void
     */
    public function processAssert(array $products, CmsIndex $cmsIndex, WishlistIndex $wishlistIndex)
    {
        $cmsIndex->getLinksBlock()->openLink("My Wish List");
        foreach ($products as $itemProduct) {
            \PHPUnit_Framework_Assert::assertFalse(
                $wishlistIndex->getItemsBlock()->getItemProductByName($itemProduct->getName())->isVisible(),
                '"' . $itemProduct->getName() . '" product is present in Wishlist.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Wishlist is empty.';
    }
}
