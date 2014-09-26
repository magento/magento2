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

use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertProductsIsAbsentInWishlist
 * Assert products is absent in Wishlist on Frontend
 */
class AssertProductsIsAbsentInWishlist extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product is not present in Wishlist on Frontend
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture[] $products
     * @param CustomerInjectable $customer
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountLogout $customerAccountLogout
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        WishlistIndex $wishlistIndex,
        $products,
        CustomerInjectable $customer,
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout
    ) {
        $customerAccountLogout->open();
        $cmsIndex->getLinksBlock()->openLink('Log In');
        $customerAccountLogin->getLoginBlock()->login($customer);
        $customerAccountIndex->open()->getAccountMenuBlock()->openMenuItem("My Wish List");
        $itemBlock = $wishlistIndex->getWishlistBlock()->getProductItemsBlock();

        foreach ($products as $itemProduct) {
            $productName = $itemProduct->getName();
            \PHPUnit_Framework_Assert::assertFalse(
                $itemBlock->getItemProductByName($productName)->isVisible(),
                'Product \'' . $productName . '\' is present in Wishlist on Frontend.'
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
        return 'Product is absent in Wishlist on Frontend.';
    }
}
