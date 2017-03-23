<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Wishlist\Test\Page\WishlistIndex;
use Magento\Wishlist\Test\Page\WishlistShare;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create Customer Account.
 * 2. Create product.
 *
 * Steps:
 * 1. Login to frontend as a Customer.
 * 2. Add product to Wish List.
 * 3. Click "Share Wish List" button.
 * 4. Fill in all data according to data set.
 * 5. Click "Share Wishlist" button.
 * 6. Perform all assertions.
 *
 * @group Wishlist
 * @ZephyrId MAGETWO-23394
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShareWishlistEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Wishlist index page.
     *
     * @var WishlistIndex
     */
    protected $wishlistIndex;

    /**
     * Wishlist share page.
     *
     * @var WishlistShare
     */
    protected $wishlistShare;

    /**
     * Prepare data.
     *
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @return array
     */
    public function __prepare(Customer $customer, CatalogProductSimple $product)
    {
        $customer->persist();
        $product->persist();

        return [
            'customer' => $customer,
            'product' => $product
        ];
    }

    /**
     * Inject pages.
     *
     * @param CmsIndex $cmsIndex
     * @param WishlistIndex $wishlistIndex
     * @param WishlistShare $wishlistShare
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        WishlistIndex $wishlistIndex,
        WishlistShare $wishlistShare
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->wishlistIndex = $wishlistIndex;
        $this->wishlistShare = $wishlistShare;
    }

    /**
     * Share wish list.
     *
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @param array $sharingInfo
     * @return void
     */
    public function test(
        Customer $customer,
        CatalogProductSimple $product,
        array $sharingInfo
    ) {
        //Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $this->objectManager->create(
            \Magento\Wishlist\Test\TestStep\AddProductsToWishlistStep::class,
            ['products' => [$product]]
        )->run();
        $this->wishlistIndex->getMessagesBlock()->waitSuccessMessage();
        $this->wishlistIndex->getWishlistBlock()->clickShareWishList();
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->wishlistShare->getSharingInfoForm()->fillForm($sharingInfo);
        $this->wishlistShare->getSharingInfoForm()->shareWishlist();
    }
}
