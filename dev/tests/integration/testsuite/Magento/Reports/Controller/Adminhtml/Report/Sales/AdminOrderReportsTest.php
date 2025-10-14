<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Controller\Adminhtml\Report\Sales;

use Magento\Catalog\Helper\Data;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetDeliveryMethod as SetDeliveryMethodFixture;
use Magento\Checkout\Test\Fixture\SetPaymentMethod as SetPaymentMethodFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Directory\Test\Fixture\CurrencyRate;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\CustomerCart;
use Magento\Sales\Api\InvoiceOrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Group as StoreGroupFixture;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\Store\Test\Fixture\Website as WebsiteFixture;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for order reports.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdminOrderReportsTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var InvoiceOrderInterface
     */
    private $invoiceOrder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartManagement = $this->_objectManager->get(CartManagementInterface::class);
        $this->invoiceOrder = $this->_objectManager->get(InvoiceOrderInterface::class);
        $this->fixtures = $this->_objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    protected function tearDown(): void
    {
        $store = $this->storeManager->getStore();
        $store->setCurrentCurrencyCode('USD');

        $registry = $this->_objectManager->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);

        $orderCollection = $this->_objectManager->create(OrderCollectionFactory::class)->create();
        foreach ($orderCollection as $order) {
            $order->delete();
        }

        $invoiceCollection = $this->_objectManager->create(InvoiceCollectionFactory::class)->create();
        foreach ($invoiceCollection as $invoice) {
            $invoice->delete();
        }

        $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Report\Order')->aggregate();

        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);

        parent::tearDown();
    }

    /**
     * Test to verify admin order reports for multi website with different display currency.
     *
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws StateException
     * @throws LocalizedException
     */
    #[
        DbIsolation(false),
        AppArea('adminhtml'),
        Config(Data::XML_PATH_PRICE_SCOPE, Data::PRICE_SCOPE_WEBSITE),
        DataFixture(WebsiteFixture::class, ['code' => 'website2'], as: 'website2'),
        DataFixture(StoreGroupFixture::class, ['website_id' => '$website2.id$'], 'group2'),
        DataFixture(StoreFixture::class, ['store_group_id' => '$group2.id$'], 'store2'),
        Config('currency/options/default', 'EUR', ScopeInterface::SCOPE_WEBSITE, 'website2'),
        Config('currency/options/allow', 'EUR', ScopeInterface::SCOPE_WEBSITE, 'website2'),
        DataFixture(CurrencyRate::class, ['USD' => ['EUR' => '0.8']]),
        DataFixture(ProductFixture::class, ['website_ids' => [1, '$website2.id$']], as: 'p1'),
        DataFixture(Customer::class, ['store_id' => '$store2.id$', 'website_id' => '$website2.id$'], as: 'customer'),
        DataFixture(CustomerCart::class, ['customer_id' => '$customer.id$'], as: 'cart', scope: 'store2'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$', 'qty' => 1]
        ),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$'], as: 'billingAddress'),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$'], as: 'shippingAddress'),
        DataFixture(SetDeliveryMethodFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetPaymentMethodFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testAdminOrderReportsForMultiWebsiteWithDifferentDisplayCurrency()
    {
        $cart = $this->fixtures->get('cart');
        $websiteId = (int) $this->fixtures->get('website2')->getId();

        $store = $this->storeManager->getStore();
        $store->setCurrentCurrencyCode('EUR');

        $orderId = $this->cartManagement->placeOrder($cart->getId());
        $this->assertNotEmpty($orderId);
        $invoiceId = $this->invoiceOrder->execute($orderId);
        $this->assertNotEmpty($invoiceId);

        $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Report\Order')->aggregate();

        $beforeCurrentDate = date('m-d-Y', strtotime('-1 day'));
        $afterCurrentDate = date('m-d-Y', strtotime('+1 day'));

        $requestArray = [
            'report_type' => 'created_at_order',
            'period_type' => 'day',
            'from' => $beforeCurrentDate,
            'to' => $afterCurrentDate,
            'show_order_statuses' => '0',
            'show_empty_rows' => '0',
            'show_actual_columns' => '0',
        ];
        $filterData = base64_encode(http_build_query($requestArray));
        $this->dispatch("backend/reports/report_sales/sales/website/{$websiteId}/filter/{$filterData}/");
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $layout = $this->_objectManager->get(LayoutInterface::class);
        $salesReportGrid = $layout->getBlock('adminhtml_sales_sales.grid');
        $blockHtml = $salesReportGrid->toHtml();
        $this->assertStringContainsString('€', $blockHtml);
    }
}
