<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer
 * 2. Create product
 *
 * Steps:
 * 1. Login as customer in frontend
 * 2. Add product to cart
 * 3. Logout
 * 4. Add product to cart as unregistered customer
 * 5. Perform all assertions
 *
 * @group Reports
 * @ZephyrId MAGETWO-27952
 */
class ProductsInCartReportEntityTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Prepare data
     *
     * @param Customer $customer
     * @return array
     */
    public function __prepare(Customer $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CmsIndex $cmsIndex
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CatalogProductView $catalogProductView
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->catalogProductView = $catalogProductView;
    }

    /**
     * Create products in cart report entity
     *
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @param string $isGuest
     * @param BrowserInterface $browser
     * @return void
     */
    public function test(
        Customer $customer,
        CatalogProductSimple $product,
        $isGuest,
        BrowserInterface $browser
    ) {
        // Preconditions
        $product->persist();

        //Steps
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
        $productUrl = $_ENV['app_frontend_url'] . $product->getUrlKey() . '.html';
        $browser->open($productUrl);
        $this->catalogProductView->getViewBlock()->addToCart($product);
        $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        if ($isGuest) {
            $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
            $browser->open($productUrl);
            $this->catalogProductView->getViewBlock()->addToCart($product);
            $this->catalogProductView->getMessagesBlock()->waitSuccessMessage();
        }
    }

    /**
     * Log out after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
    }
}
