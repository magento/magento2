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

namespace Magento\Reports\Test\TestCase;

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Product\CatalogProductView;

/**
 * Test Creation for AbandonedCartsReportEntity
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create simple product
 * 2. Create customer
 * 3. Go to frontend
 * 4. Login as customer
 * 5. Add simple product to cart
 * 6. Logout
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Reports > Abandoned Carts
 * 3. Click "Reset Filter"
 * 4. Perform all assertions
 *
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-28558
 */
class AbandonedCartsReportEntityTest extends Injectable
{
    /**
     * Cms Index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer Account Login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Catalog Product View page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Browser interface
     *
     * @var Browser
     */
    protected $browser;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Inject pages
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param Browser $browser
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        Browser $browser,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->browser = $browser;
        $this->catalogProductView = $catalogProductView;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create product and add it to cart
     *
     * @param string $products
     * @param CustomerInjectable $customer
     * @return array
     */
    public function test($products, CustomerInjectable $customer)
    {
        // Precondition
        $products = $this->createProducts($products);
        $customer->persist();
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink("Log In");
        $this->customerAccountLogin->getLoginBlock()->login($customer);
        $this->addProductsToCart($products);
        $this->cmsIndex->getLinksBlock()->openLink("Log Out");

        return ['products' => $products];
    }

    /**
     * Create products
     *
     * @param string $products
     * @return array
     */
    protected function createProducts($products)
    {
        $createProductsStep = $this->objectManager->create(
            'Magento\Catalog\Test\TestStep\CreateProductsStep',
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    protected function addProductsToCart(array $products)
    {
        $addProductsToCart = $this->objectManager->create(
            'Magento\Checkout\Test\TestStep\AddProductsToTheCartStep',
            ['products' => $products]
        );
        $addProductsToCart->run();
    }
}
