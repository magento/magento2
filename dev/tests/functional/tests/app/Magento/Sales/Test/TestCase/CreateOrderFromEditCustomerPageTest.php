<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Sales\Test\Constraint\AssertCartSectionIsEmptyOnBackendOrderPage;
use Magento\Sales\Test\Constraint\AssertCartSectionWithProductsOnBackendOrderPage;
use Magento\Sales\Test\Constraint\AssertItemsOrderedSectionContainsProducts;
use Magento\Sales\Test\Constraint\AssertItemsOrderedSectionOnBackendOrderIsEmpty;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Store\Test\Fixture\Store;
use Magento\Wishlist\Test\Constraint\AssertCustomerWishlistOnBackendIsEmpty;
use Magento\Wishlist\Test\Constraint\AssertProductsIsPresentInCustomerBackendWishlist;

/**
 * Preconditions:
 * 1. Apply configuration settings.
 * 2. Create customer.
 * 3. Create products.
 *
 * Steps:
 * 1. Log in to Admin.
 * 2. Go to Customers -> All Customers page.
 * 3. Open created customer to edit.
 * 4. Click Create Order button.
 * 5. Select Store.
 * 6. Click Add Products button.
 * 7. Select corresponded Products.
 * 8. Click Add Selected Product(s) to Order button.
 * 9. Perform assertions.
 * 10. Select Move to Wish List action for Products from Test Data.
 * 11. Click Update Items and Quantities button.
 * 12. Perform assertions.
 * 13. Select Add to Order in "Wish List" data grid for correspondent Products.
 * 14. Click Update Changes button.
 * 15. Perform assertions.
 * 16. Select Move to Shopping Cart action for Products from Test Data.
 * 17. Click Update Items and Quantities button.
 * 18. Perform assertions.
 * 19. Select Add to Order in "Shopping Cart" data grid for correspondent Products.
 * 20. Click Update Changes button.
 * 21. Perform assertions.
 * 22. Fill billing and shipping addresses.
 * 23. Click Submit Order button.
 * 24. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-19454
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateOrderFromEditCustomerPageTest extends Injectable
{
    /**
     * Configuration settings.
     *
     * @var string
     */
    private $configData;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    private $stepFactory;

    /**
     * Customer index page.
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * Store fixture.
     *
     * @var Store
     */
    protected $store;

    /**
     * Order Create Index page.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Assert that Items Ordered section on Create Order page on backend contains products.
     *
     * @var AssertItemsOrderedSectionContainsProducts
     */
    protected $assertItemsOrderedSectionContainsProducts;

    /**
     * Assert that customer's Wish List section on Order Create backend page is empty.
     *
     * @var AssertCustomerWishlistOnBackendIsEmpty
     */
    protected $assertCustomerWishlistOnBackendIsEmpty;

    /**
     * Assert that customer's Shopping Cart section on Order Create backend page is empty.
     *
     * @var AssertCartSectionIsEmptyOnBackendOrderPage
     */
    protected $assertCartSectionIsEmptyOnBackendOrderPage;

    /**
     * Assert that products added to wishlist are present on Customers account on backend.
     *
     * @var AssertProductsIsPresentInCustomerBackendWishlist
     */
    protected $assertProductsIsPresentInCustomerBackendWishlist;

    /**
     * Assert that Items Ordered section on Create Order page on backend is empty.
     *
     * @var AssertItemsOrderedSectionOnBackendOrderIsEmpty
     */
    protected $assertItemsOrderedSectionOnBackendOrderIsEmpty;

    /**
     * Assert that customer's Shopping Cart section on Order Create backend page contains products.
     *
     * @var AssertCartSectionWithProductsOnBackendOrderPage
     */
    protected $assertCartSectionWithProductsOnBackendOrderPage;

    /**
     * Prepare test data.
     *
     * @param AssertItemsOrderedSectionOnBackendOrderIsEmpty $assertItemsOrderedSectionOnBackendOrderIsEmpty
     * @param AssertCartSectionWithProductsOnBackendOrderPage $assertCartSectionWithProductsOnBackendOrderPage
     * @param AssertProductsIsPresentInCustomerBackendWishlist $assertProductsIsPresentInCustomerBackendWishlist
     * @return void
     */
    public function __prepare(
        AssertItemsOrderedSectionOnBackendOrderIsEmpty $assertItemsOrderedSectionOnBackendOrderIsEmpty,
        AssertCartSectionWithProductsOnBackendOrderPage $assertCartSectionWithProductsOnBackendOrderPage,
        AssertProductsIsPresentInCustomerBackendWishlist $assertProductsIsPresentInCustomerBackendWishlist
    ) {
        $this->assertItemsOrderedSectionOnBackendOrderIsEmpty = $assertItemsOrderedSectionOnBackendOrderIsEmpty;
        $this->assertCartSectionWithProductsOnBackendOrderPage = $assertCartSectionWithProductsOnBackendOrderPage;
        $this->assertProductsIsPresentInCustomerBackendWishlist = $assertProductsIsPresentInCustomerBackendWishlist;
    }

    /**
     * Inject pages.
     *
     * @param TestStepFactory $stepFactory
     * @param CustomerIndex $customerIndex
     * @param CustomerIndexEdit $customerIndexEdit
     * @param Store $store
     * @param SalesOrderView $salesOrderView
     * @param OrderCreateIndex $orderCreateIndex
     * @param AssertItemsOrderedSectionContainsProducts $assertItemsOrderedSectionContainsProducts
     * @param AssertCustomerWishlistOnBackendIsEmpty $assertCustomerWishlistOnBackendIsEmpty
     * @param AssertCartSectionIsEmptyOnBackendOrderPage $assertCartSectionIsEmptyOnBackendOrderPage
     * @return void
     */
    public function __inject(
        TestStepFactory $stepFactory,
        CustomerIndex $customerIndex,
        CustomerIndexEdit $customerIndexEdit,
        Store $store,
        SalesOrderView $salesOrderView,
        OrderCreateIndex $orderCreateIndex,
        AssertItemsOrderedSectionContainsProducts $assertItemsOrderedSectionContainsProducts,
        AssertCustomerWishlistOnBackendIsEmpty $assertCustomerWishlistOnBackendIsEmpty,
        AssertCartSectionIsEmptyOnBackendOrderPage $assertCartSectionIsEmptyOnBackendOrderPage
    ) {
        $this->stepFactory = $stepFactory;
        $this->customerIndex = $customerIndex;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->store = $store;
        $this->salesOrderView = $salesOrderView;
        $this->orderCreateIndex = $orderCreateIndex;
        $this->assertItemsOrderedSectionContainsProducts = $assertItemsOrderedSectionContainsProducts;
        $this->assertCustomerWishlistOnBackendIsEmpty = $assertCustomerWishlistOnBackendIsEmpty;
        $this->assertCartSectionIsEmptyOnBackendOrderPage = $assertCartSectionIsEmptyOnBackendOrderPage;
    }

    /**
     * Runs sales order on backend.
     *
     * @param Customer $customer
     * @param array $payment
     * @param Address $billingAddress
     * @param string|null $configData
     * @param array|null $shipping
     * @param array|null $products
     * @return array
     */
    public function test(
        Customer $customer,
        array $payment,
        Address $billingAddress,
        $configData = null,
        array $shipping = null,
        array $products = []
    ) {
        // Preconditions:
        $this->configData = $configData;
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $products = $this->stepFactory->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        )->run()['products'];

        // Steps:
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $customer->getEmail()]);
        $this->customerIndexEdit->getPageActionsBlock()->createOrder();
        if ($this->orderCreateIndex->getStoreBlock()->isVisible()) {
            $this->orderCreateIndex->getStoreBlock()->selectStoreView($this->store);
        }
        $this->stepFactory->create(
            \Magento\Sales\Test\TestStep\AddProductsStep::class,
            ['products' => $products]
        )->run();
        $createBlock = $this->orderCreateIndex->getCreateBlock();
        $this->assertItemsOrderedSectionContainsProducts->processAssert($this->orderCreateIndex, $products);
        $this->assertCustomerWishlistOnBackendIsEmpty->processAssert($this->orderCreateIndex);
        $this->assertCartSectionIsEmptyOnBackendOrderPage->processAssert($this->orderCreateIndex);
        foreach ([$products[0], $products[2]] as $product) {
            $createBlock->getItemsBlock()->selectItemAction($product, 'Move to Wish List');
        }
        $createBlock->updateItems();
        $this->assertItemsOrderedSectionContainsProducts->processAssert($this->orderCreateIndex, [$products[1]]);
        $this->assertProductsIsPresentInCustomerBackendWishlist
            ->processAssert($customer, $this->customerIndexEdit, [$products[0], $products[2]]);
        $this->assertCartSectionIsEmptyOnBackendOrderPage->processAssert($this->orderCreateIndex);
        $this->orderCreateIndex->getSidebarWishlistBlock()->selectItemToAddToOrder($products[0], 1);
        $this->orderCreateIndex->getSidebarWishlistBlock()->selectItemToAddToOrder($products[2], 1);
        $this->orderCreateIndex->getBackendOrderSidebarBlock()->updateChangesClick();
        $createBlock->waitOrderItemsGrid();
        $this->assertItemsOrderedSectionContainsProducts->processAssert($this->orderCreateIndex, $products);
        $this->assertProductsIsPresentInCustomerBackendWishlist
            ->processAssert($customer, $this->customerIndexEdit, [$products[0], $products[2]]);
        $this->assertCartSectionIsEmptyOnBackendOrderPage->processAssert($this->orderCreateIndex);
        foreach ($products as $product) {
            $createBlock->getItemsBlock()->selectItemAction($product, 'Move to Shopping Cart');
        }
        $createBlock->updateItems();
        $this->assertItemsOrderedSectionOnBackendOrderIsEmpty->processAssert($this->orderCreateIndex);
        $this->assertProductsIsPresentInCustomerBackendWishlist
            ->processAssert($customer, $this->customerIndexEdit, [$products[0], $products[2]]);
        $this->assertCartSectionWithProductsOnBackendOrderPage->processAssert($this->orderCreateIndex, $products);
        foreach ([$products[0], $products[2]] as $product) {
            $this->orderCreateIndex->getBackendOrderSidebarBlock()->selectItemToAddToOrder($product);
        }
        $this->orderCreateIndex->getBackendOrderSidebarBlock()->updateChangesClick();
        $createBlock->waitOrderItemsGrid();
        $this->assertItemsOrderedSectionContainsProducts->processAssert(
            $this->orderCreateIndex,
            [$products[0], $products[2]]
        );
        $this->assertProductsIsPresentInCustomerBackendWishlist
            ->processAssert($customer, $this->customerIndexEdit, [$products[0], $products[2]]);
        $this->assertCartSectionWithProductsOnBackendOrderPage->processAssert($this->orderCreateIndex, [$products[1]]);
        $this->stepFactory->create(
            \Magento\Sales\Test\TestStep\FillBillingAddressStep::class,
            [
                'orderCreateIndex' => $this->orderCreateIndex,
                'billingAddress' => $billingAddress,
                'setShippingAddress' => true
            ]
        )->run();
        $this->stepFactory->create(
            \Magento\Sales\Test\TestStep\SelectPaymentMethodForOrderStep::class,
            ['payment' => $payment]
        )->run();
        $this->stepFactory->create(
            \Magento\Sales\Test\TestStep\SelectShippingMethodForOrderStep::class,
            ['shipping' => $shipping]
        )->run();
        $createBlock->submitOrder();
        $orderId = trim($this->salesOrderView->getTitleBlock()->getTitle(), '#');
        return [
            'orderId' => $orderId,
            'customer' => $customer,
            'productsInCart' => [
                $products[1]
            ]
        ];
    }

    /**
     * Deleting cart price rules and customer segments.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
