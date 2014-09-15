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

use Mtf\TestCase\Injectable;
use Mtf\Client\Browser;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Test Creation for ProductsInCartReportEntity
 *
 * Test Flow:
 *
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
 * @group Reports_(MX)
 * @ZephyrId MAGETWO-27952
 */
class ProductsInCartReportEntity extends Injectable
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Product view page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Customer logout page
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Prepare data
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout,
        CatalogProductView $catalogProductView
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountLogout = $customerAccountLogout;
        $this->catalogProductView = $catalogProductView;
    }

    /**
     * Create products in cart report entity
     *
     * @param CustomerInjectable $customer
     * @param CatalogProductSimple $product
     * @param string $isGuest
     * @param Browser $browser
     * @return void
     */
    public function test(
        CustomerInjectable $customer,
        CatalogProductSimple $product,
        $isGuest,
        Browser $browser
    ) {
        // Preconditions
        $product->persist();

        //Steps
        $this->cmsIndex->open()->getLinksBlock()->openLink("Log In");
        $this->customerAccountLogin->getLoginBlock()->login($customer);
        $productUrl = $_ENV['app_frontend_url'] . $product->getUrlKey() . '.html';
        $browser->open($productUrl);
        $this->catalogProductView->getViewBlock()->addToCart($product);
        if ($isGuest) {
            $this->customerAccountLogout->open();
            $browser->open($productUrl);
            $this->catalogProductView->getViewBlock()->addToCart($product);
        }
    }

    /**
     * Log out after test
     *
     * @return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
