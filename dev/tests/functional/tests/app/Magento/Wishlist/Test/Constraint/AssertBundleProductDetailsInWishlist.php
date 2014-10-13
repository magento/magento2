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
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\InjectableFixture;

/**
 * Class AssertBundleProductDetailsInWishlist
 * Assert that the correct option details are displayed on the "View Details" tool tip.
 */
class AssertBundleProductDetailsInWishlist extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that the correct option details are displayed on the "View Details" tool tip.
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @param WishlistIndex $wishlistIndex
     * @param InjectableFixture $product
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex,
        WishlistIndex $wishlistIndex,
        InjectableFixture $product,
        FixtureFactory $fixtureFactory
    ) {
        $cmsIndex->getLinksBlock()->openLink('My Account');
        $customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Wish List');
        $productBlock = $wishlistIndex->getItemsBlock()->getItemProduct($product);
        $actualOptions = $this->prepareOptions($productBlock->getOptions());
        $cartFixture = $fixtureFactory->createByCode('cart', ['data' => ['items' => ['products' => [$product]]]]);
        $bundleOptions = $cartFixture->getItems()[0]->getData()['options'];
        $expectedOptions = [];
        foreach ($bundleOptions as $option) {
            $expectedOptions[] = $option['value'];
        }

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedOptions,
            $actualOptions,
            "Expected bundle options are not equals to actual"
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return "Expected bundle options are equal to actual";
    }

    /**
     * Prepare bundle options for comparing
     *
     * @param array $options
     * @return array
     */
    protected function prepareOptions($options)
    {
        foreach ($options as &$option) {
            $chunks = explode(' ', $option);
            $lastChunk = array_pop($chunks);
            $lastChunk = preg_replace("/^\\D*\\s*([\\d,\\.]+)\\s*\\D*$/", '\1', $lastChunk);
            array_push($chunks, $lastChunk);
            $option = implode(' ', $chunks);
        }
        return $options;
    }
}
