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

use Mtf\Client\Browser;
use Mtf\TestCase\Injectable;
use Mtf\Fixture\FixtureFactory;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Test creation for DeleteProductFromCustomerWishlistOnBackend
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create product
 * 3. Login to frontend as a customer
 * 4. Add product to Wish List
 *
 * Steps:
 * 1. Go to Backend
 * 2. Go to Customers > All Customers
 * 3. Open the customer
 * 4. Open wishlist tab
 * 5. Click 'Delete'
 * 6. Perform assertions
 *
 * @group Wishlist_(CS)
 * @ZephyrId MAGETWO-27813
 */
class DeleteProductFromCustomerWishlistOnBackendTest extends Injectable
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
     * Page CustomerAccountLogout
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Page of all customer grid
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer edit page
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

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
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        CustomerAccountLogout $customerAccountLogout,
        CatalogProductView $catalogProductView,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountLogout = $customerAccountLogout;
        $this->catalogProductView = $catalogProductView;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
    }

    /**
     * Delete product from customer wishlist on backend
     *
     * @param Browser $browser
     * @param CustomerInjectable $customer
     * @param FixtureFactory $fixtureFactory
     * @param string $product
     * @return array
     */
    public function test(Browser $browser, CustomerInjectable $customer, FixtureFactory $fixtureFactory, $product)
    {
        $this->markTestIncomplete('MAGETWO-27949');
        //Preconditions
        list($fixture, $dataSet) = explode('::', $product);
        $product = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
        $product->persist();
        $this->loginCustomer($customer);
        $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToWishlist();

        //Steps
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $customerForm = $this->customerIndexEdit->getCustomerForm();
        $customerForm->openTab('wishlist');
        $filter = ['product_name' => $product->getName()];
        $customerForm->getTabElement('wishlist')->getSearchGridBlock()->searchAndDelete($filter);

        return ['products' => [$product]];
    }

    /**
     * Login customer
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomer(CustomerInjectable $customer)
    {
        $this->cmsIndex->open();
        if (!$this->cmsIndex->getLinksBlock()->isLinkVisible('Log Out')) {
            $this->cmsIndex->getLinksBlock()->openLink('Log In');
            $this->customerAccountLogin->getLoginBlock()->login($customer);
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
