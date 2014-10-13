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

namespace Magento\Wishlist\Test\TestCase;

use Magento\Customer\Test\Page\CustomerAccountLogout;
use Mtf\TestCase\Injectable;
use Mtf\Client\Driver\Selenium\Browser;
use Mtf\Fixture\InjectableFixture;
use Mtf\ObjectManager;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\CustomerInjectable;

/**
 * Test Creation for AddProductToWishlistEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Customer is registered
 * 2. Product is created
 *
 * Steps:
 * 1. Login as a customer
 * 2. Navigate to catalog page
 * 3. Add created product to Wishlist according to dataSet
 * 4. Perform all assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-29045
 */
class AddProductToWishlistEntityTest extends Injectable
{
    /**
     * Catalog product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Customer Account Logout page
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Browser object
     *
     * @var Browser
     */
    protected $browser;

    /**
     * ObjectManager object
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Prepare data for test
     *
     * @param CustomerInjectable $customer
     * @param Browser $browser
     * @param ObjectManager $objectManager
     * @return array
     */
    public function __prepare(CustomerInjectable $customer, Browser $browser, ObjectManager $objectManager)
    {
        $this->browser = $browser;
        $this->objectManager = $objectManager;
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CatalogProductView $catalogProductView
     * @param CustomerAccountLogout $customerAccountLogout
     * @return void
     */
    public function __inject(
        CatalogProductView $catalogProductView,
        CustomerAccountLogout $customerAccountLogout
    ) {
        $this->catalogProductView = $catalogProductView;
        $this->customerAccountLogout = $customerAccountLogout;
    }

    /**
     * Run Add Product To Wishlist test
     *
     * @param CustomerInjectable $customer
     * @param string $product
     * @return array
     */
    public function test(CustomerInjectable $customer, $product)
    {
        $this->markTestIncomplete('Bug: MAGETWO-27949');
        $product = $this->createProduct($product);

        // Steps:
        $this->loginCustomer($customer);
        $this->addProductToWishlist($product);

        return ['product' => $product];
    }

    /**
     * Create product
     *
     * @param string $product
     * @return InjectableFixture
     */
    protected function createProduct($product)
    {
        $createProducts = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $product]
        );
        return $createProducts->run()['products'][0];
    }

    /**
     * Login customer
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomer(CustomerInjectable $customer)
    {
        $customerLogin = $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $customerLogin->run();
    }

    /**
     * Add product to wishlist
     *
     * @param InjectableFixture $product
     * @return void
     */
    protected function addProductToWishlist($product)
    {
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $viewBlock = $this->catalogProductView->getViewBlock();
        $viewBlock->fillOptions($product);
        $viewBlock->addToWishlist();
    }

    /**
     * Logout customer from frontend account
     *
     * return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
