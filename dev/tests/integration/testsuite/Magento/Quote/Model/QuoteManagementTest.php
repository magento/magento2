<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Vat;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Observer\Frontend\Quote\Address\VatValidator;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class for testing QuoteManagement model
 *
 * @see \Magento\Quote\Model\QuoteManagement
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteManagementTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var GetQuoteByReservedOrderId
     */
    private $getQuoteByReservedOrderId;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Creates order with product that has child items.
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     *
     * @return void
     */
    public function testSubmit(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get($orderId);
        $orderItems = $order->getItems();
        $this->assertCount(3, $orderItems);
        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductType() == Type::TYPE_SIMPLE) {
                $this->assertNotEmpty($orderItem->getParentItem(), 'Parent is not set for child product');
                $this->assertNotEmpty($orderItem->getParentItemId(), 'Parent is not set for child product');
            }
        }
    }

    /**
     * Verify guest customer place order with auto-group assigment.
     *
     * @magentoDataFixture Magento/Sales/_files/guest_quote_with_addresses.php
     *
     * @magentoConfigFixture default_store customer/create_account/auto_group_assign 1
     * @magentoConfigFixture default_store customer/create_account/tax_calculation_address_type shipping
     * @magentoConfigFixture default_store customer/create_account/viv_intra_union_group 2
     * @magentoConfigFixture default_store customer/create_account/viv_on_each_transaction 1
     *
     * @return void
     */
    public function testSubmitGuestCustomer(): void
    {
        $this->mockVatValidation();
        $quote = $this->getQuoteByReservedOrderId->execute('guest_quote');
        $this->cartManagement->placeOrder($quote->getId());
        $quoteAfterOrderPlaced = $this->getQuoteByReservedOrderId->execute('guest_quote');
        self::assertEquals(2, $quoteAfterOrderPlaced->getCustomerGroupId());
        self::assertEquals(3, $quoteAfterOrderPlaced->getCustomerTaxClassId());
    }

    /**
     * Creates order with purchase_order payment method
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function testSubmitWithPurchaseOrder(): void
    {
        $paymentMethodName = 'purchaseorder';
        $poNumber = '12345678';
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $quote->getPayment()->setPoNumber($poNumber);
        $quote->collectTotals()->save();
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get($orderId);
        $orderItems = $order->getItems();
        $this->assertCount(1, $orderItems);
        $payment = $order->getPayment();
        $this->assertEquals($paymentMethodName, $payment->getMethod());
        $this->assertEquals($poNumber, $payment->getPoNumber());
    }

    /**
     * Creates order with purchase_order payment method without po_number
     *
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_purchase_order.php
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function testSubmitWithPurchaseOrderWithException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Purchase order number is a required field.');

        $quote = $this->getQuoteByReservedOrderId->execute('test_order_1');
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create order with product that has child items and one of them was deleted.
     *
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 1
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmitWithDeletedItem(): void
    {
        $this->productRepository->deleteById('simple-2');
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $this->expectExceptionObject(
            new LocalizedException(__('Some of the products below do not have all the required options.'))
        );
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create order with product that has child items and one of them
     * was deleted when item data check is disabled on quote load.
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/quote_with_bundle.php
     */
    public function testSubmitWithDeletedItemWithDisabledInventoryCheck(): void
    {
        $this->productRepository->deleteById('simple-2');
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create order with item of stock during checkout.
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 1
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDbIsolation enabled
     */
    public function testSubmitWithItemOutOfStock(): void
    {
        $this->makeProductOutOfStock('simple');
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $this->expectExceptionObject(new LocalizedException(__('Some of the products are out of stock.')));
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create order with item of stock during checkout
     * when item data check is disabled on quote load.
     * @magentoConfigFixture cataloginventory/options/enable_inventory_check 0
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDbIsolation enabled
     */
    public function testSubmitWithItemOutOfStockWithDisabledInventoryCheck(): void
    {
        $this->makeProductOutOfStock('simple');
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $this->expectExceptionObject(
            new LocalizedException(
                __('The shipping method is missing. Select the shipping method and try again.')
            )
        );
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Tries to create an order using quote with empty customer email.
     *
     * Order should not start placing if order validation is failed.
     *
     * @magentoDataFixture Magento/Quote/Fixtures/quote_without_customer_email.php
     *
     * @return void
     */
    public function testSubmitWithEmptyCustomerEmail(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $orderManagement = $this->createMock(OrderManagementInterface::class);
        $orderManagement->expects($this->never())
            ->method('place');
        $cartManagement = $this->objectManager->create(
            CartManagementInterface::class,
            ['orderManagement' => $orderManagement]
        );
        $this->expectExceptionObject(new LocalizedException(__('Email has a wrong format')));
        try {
            $cartManagement->placeOrder($quote->getId());
        } catch (ExpectationFailedException $e) {
            $this->fail('Place order method was not expected to be called if order validation is failed');
        }
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testAssignCustomerToQuote(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $result = $this->cartManagement->assignCustomer($quote->getId(), $customer->getId(), $customer->getStoreId());
        $this->assertTrue($result);
        $customerQuote = $this->cartManagement->getCartForCustomer($customer->getId());
        $this->assertEquals($quote->getId(), $customerQuote->getId());
        $this->assertEquals($customer->getId(), $customerQuote->getCustomerId());
        $this->assertEquals($customer->getEmail(), $customerQuote->getCustomerEmail());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product_saved.php
     * @magentoDataFixture Magento/Customer/_files/customer_for_second_website.php
     *
     * @return void
     */
    public function testAssignCustomerFromAnotherWebsiteToQuote(): void
    {
        $websiteId = $this->storeManager->getWebsite('test')->getId();
        $customer = $this->customerRepository->get('customer@example.com', $websiteId);
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_simple_product_without_address');
        $this->expectExceptionObject(
            new StateException(
                __('The customer can\'t be assigned to the cart. The cart belongs to a different store.')
            )
        );
        $this->cartManagement->assignCustomer($quote->getId(), $customer->getId(), $quote->getStoreId());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_customer_without_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_with_uk_address.php
     *
     * @return void
     */
    public function testAssignCustomerToQuoteAlreadyHaveCustomer(): void
    {
        $customer = $this->customerRepository->get('customer_uk_address@test.com');
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_customer_without_address');
        $this->expectExceptionObject(
            new StateException(__('The customer can\'t be assigned to the cart because the cart isn\'t anonymous.'))
        );
        $this->cartManagement->assignCustomer($quote->getId(), $customer->getId(), $quote->getStoreId());
    }

    /**
     * Makes provided product as out of stock.
     *
     * @param string $sku
     * @return void
     */
    private function makeProductOutOfStock(string $sku): void
    {
        $product = $this->productRepository->get($sku);
        $extensionAttributes = $product->getExtensionAttributes();
        $stockItem = $extensionAttributes->getStockItem();
        $stockItem->setIsInStock(false);
        $this->productRepository->save($product);
    }

    /**
     * Makes customer vat validator 'check vat number' response successful.
     *
     * @return void
     */
    private function mockVatValidation(): void
    {
        $vatMock = $this->getMockBuilder(Vat::class)
            ->setConstructorArgs(
                [
                    'scopeConfig' => $this->objectManager->get(ScopeConfigInterface::class),
                    'logger' => $this->objectManager->get(LoggerInterface::class),
                ]
            )
            ->onlyMethods(['checkVatNumber'])
            ->getMock();
        $gatewayResponse = new DataObject([
            'is_valid' => true,
            'request_date' => 'testData',
            'request_identifier' => 'testRequestIdentifier',
            'request_success' => true,
        ]);
        $vatMock->method('checkVatNumber')->willReturn($gatewayResponse);
        $this->objectManager->removeSharedInstance(CollectTotalsObserver::class);
        $this->objectManager->removeSharedInstance(VatValidator::class);
        $this->objectManager->removeSharedInstance(Vat::class);
        $this->objectManager->addSharedInstance($vatMock, Vat::class);
    }
}
