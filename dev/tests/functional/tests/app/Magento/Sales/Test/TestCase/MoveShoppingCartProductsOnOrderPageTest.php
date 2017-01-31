<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create product.
 * 3. Add product to cart.
 *
 * Steps:
 * 1. Open Customers > All Customers.
 * 2. Search and open customer from preconditions.
 * 3. Click Create Order.
 * 4. Check product in Shopping Cart section.
 * 5. Click Update Changes.
 * 6. Perform all assertions.
 *
 * @group Order_Management_(CS)
 * @ZephyrId MAGETWO-28540
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MoveShoppingCartProductsOnOrderPageTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    /* end tags */

    /**
     * Customer logout page
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Browser instance
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Catalog product page
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Customer index page
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer index edit page
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Order create index page
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Prepare data.
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
     * Inject pages.
     *
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CatalogProductView $catalogProductView
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param BrowserInterface $browser
     * @param OrderCreateIndex $orderCreateIndex
     * @return void
     */
    public function __inject(
        CustomerAccountLogout $customerAccountLogout,
        CatalogProductView $catalogProductView,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        BrowserInterface $browser,
        OrderCreateIndex $orderCreateIndex
    ) {
        $this->customerAccountLogout = $customerAccountLogout;
        $this->catalogProductView = $catalogProductView;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->browser = $browser;
        $this->orderCreateIndex = $orderCreateIndex;
    }

    /**
     * Create order from customer page (cartActions).
     *
     * @param Customer $customer
     * @param string $product
     * @return array
     */
    public function test(Customer $customer, $product)
    {
        //Preconditions
        // Create product
        $product = $this->objectManager->create(
            '\Magento\Catalog\Test\TestStep\CreateProductStep',
            ['product' => $product]
        )->run()['product'];
        // Login under customer
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        )->run();
        $this->browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
        $this->catalogProductView->getViewBlock()->addToCart($product);

        //Steps
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $this->customerIndexEdit->getPageActionsBlock()->createOrder();
        $this->orderCreateIndex->getStoreBlock()->selectStoreView();
        $this->orderCreateIndex->getCustomerActivitiesBlock()->getShoppingCartItemsBlock()
            ->addProductsToOrder([$product]);
        $this->orderCreateIndex->getCustomerActivitiesBlock()->updateChanges();

        return ['products' => [$product]];
    }

    /**
     * Log out after test.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
