<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Bundle\Test\Fixture\AddProductToCart as AddBundleProductToCartFixture;
use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\ConfigurableProduct\Test\Fixture\AddProductToCart as AddConfigurableProductToCartFixture;
use Magento\ConfigurableProduct\Test\Fixture\Attribute as AttributeFixture;
use Magento\ConfigurableProduct\Test\Fixture\Product as ConfigurableProductFixture;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\CartItemInterfaceFactory;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;

/**
 * Tests for quote model.
 *
 * @see \Magento\Quote\Model\Quote
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QuoteTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var GetQuoteByReservedOrderId */
    private $getQuoteByReservedOrderId;

    /** @var QuoteFactory */
    private $quoteFactory;

    /** @var DataObjectHelper */
    private $dataObjectHelper;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerInterfaceFactory */
    private $customerDataFactory;

    /** @var CustomerFactory */
    private $customerFactory;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CartItemInterfaceFactory */
    private $itemFactory;

    /** @var CustomerResourceModel */
    private $customerResourceModel;

    /** @var int */
    private $customerIdToDelete;

    /** @var GroupFactory */
    private $groupFactory;

    /** @var ExtensibleDataObjectConverter */
    private $extensibleDataObjectConverter;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
        $this->getQuoteByReservedOrderId = $this->objectManager->get(GetQuoteByReservedOrderId::class);
        $this->quoteFactory = $this->objectManager->get(QuoteFactory::class);
        $this->dataObjectHelper = $this->objectManager->get(DataObjectHelper::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->customerDataFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->customerFactory = $this->objectManager->get(CustomerFactory::class);
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->itemFactory = $this->objectManager->get(CartItemInterfaceFactory::class);
        $this->customerResourceModel = $this->objectManager->get(CustomerResourceModel::class);
        $this->groupFactory = $this->objectManager->get(GroupFactory::class);
        $this->extensibleDataObjectConverter = $this->objectManager->get(ExtensibleDataObjectConverter::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->customerIdToDelete) {
            $this->customerRepository->deleteById($this->customerIdToDelete);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @return void
     */
    public function testCollectTotalsWithVirtual(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $product = $this->productRepository->get('virtual-product', false, null, true);
        $quote->addProduct($product);
        $quote->collectTotals();
        $this->assertEquals(2, $quote->getItemsQty());
        $this->assertEquals(1, $quote->getVirtualItemsQty());
        $this->assertEquals(20, $quote->getGrandTotal());
        $this->assertEquals(20, $quote->getBaseGrandTotal());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     *
     * @return void
     */
    public function testGetAddressWithVirtualProduct(): void
    {
        $quote = $this->objectManager->create(Quote::class);
        $billingAddress = $this->addressFactory->create();
        $billingAddress->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('US')
            ->setRegion('TX')
            ->setCity('Austin')
            ->setStreet('1000 West Parmer Line')
            ->setPostcode('11501')
            ->setTelephone('123456789');
        $quote->setBillingAddress($billingAddress);
        $shippingAddress = $this->addressFactory->create();
        $shippingAddress->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('US')
            ->setRegion('NJ')
            ->setCity('Newark')
            ->setStreet('2775  Granville Lane')
            ->setPostcode('07102')
            ->setTelephone('9734685221');
        $quote->setShippingAddress($shippingAddress);
        $product = $this->productRepository->get('virtual-product', false, null, true);
        $quote->addProduct($product);
        $quote->save();
        $expectedAddress = $quote->getBillingAddress();
        $this->assertEquals($expectedAddress, $quote->getAllItems()[0]->getAddress());
    }

    /**
     * @return void
     */
    public function testSetCustomerData(): void
    {
        $quote = $this->quoteFactory->create();
        $expected = $this->getCustomerDataArray();
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray($customer, $expected, CustomerInterfaceFactory::class);
        $this->assertEquals($expected, $this->extensibleDataObjectConverter->toFlatArray($customer));
        $quote->setCustomer($customer);
        $customer = $quote->getCustomer();
        $this->assertEquals($expected, $this->extensibleDataObjectConverter->toFlatArray($customer));
        $this->assertEquals($expected[CustomerInterface::EMAIL], $quote->getCustomerEmail());
        $this->assertEquals($expected[CustomerInterface::FIRSTNAME], $quote->getCustomerFirstname());
        $this->assertEquals($expected[CustomerInterface::LASTNAME], $quote->getCustomerLastname());
        $this->assertEquals($expected[CustomerInterface::MIDDLENAME], $quote->getCustomerMiddlename());
    }

    /**
     * @magentoAppArea adminhtml
     *
     * @return void
     */
    public function testUpdateCustomerData(): void
    {
        $quote = $this->quoteFactory->create();
        $expected = $this->getCustomerDataArray();
        unset($expected[CustomerInterface::ID]);
        $customerDataSet = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray($customerDataSet, $expected, CustomerInterface::class);
        $this->assertEquals($expected, $this->extensibleDataObjectConverter->toFlatArray($customerDataSet));
        $customer = $this->customerRepository->save($customerDataSet);
        $this->customerIdToDelete = $customer->getId();
        $quote->setCustomer($customerDataSet);
        $expected = $this->getCustomerDataArray();
        $expected[CustomerInterface::EMAIL] = 'test@example.com';
        $customerDataUpdated = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray($customerDataUpdated, $expected, CustomerInterface::class);
        $quote->updateCustomerData($customerDataUpdated);
        $customer = $quote->getCustomer();
        $actual = $this->extensibleDataObjectConverter->toFlatArray($customer);
        foreach ($expected as $item) {
            $this->assertContains($item, $actual);
        }
        $this->assertEquals('test@example.com', $quote->getCustomerEmail());
    }

    /**
     * Customer data is set to quote (which contains valid group ID).
     *
     * @return void
     */
    public function testGetCustomerGroupFromCustomer(): void
    {
        $customerGroupId = 3;
        $customerData = $this->customerDataFactory->create()->setId(1)->setGroupId($customerGroupId);
        $quote = $this->quoteFactory->create();
        $quote->setCustomer($customerData);
        $quote->unsetData('customer_group_id');
        $this->assertEquals($customerGroupId, $quote->getCustomerGroupId(), "Customer group ID is invalid");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @return void
     */
    public function testGetCustomerTaxClassId(): void
    {
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        $fixtureGroupId = $this->groupFactory->create()->load($fixtureGroupCode, 'customer_group_code')->getId();
        $quote = $this->quoteFactory->create();
        $quote->setCustomerGroupId($fixtureGroupId);
        $this->assertEquals($fixtureTaxClassId, $quote->getCustomerTaxClassId(), 'Customer tax class ID is invalid.');
    }

    /**
     * Billing and shipping address arguments are not passed, customer has default billing and shipping addresses.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @return void
     */
    public function testAssignCustomerWithAddressChangeAddressesNotSpecified(): void
    {
        $quote = $this->quoteFactory->create();
        $customerData = $this->prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        $quote->assignCustomerWithAddressChange($customerData);
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');
        $expectedBillingAddressData = [
            AddressInterface::KEY_STREET => 'Green str, 67',
            AddressInterface::KEY_TELEPHONE => 3468676,
            AddressInterface::KEY_POSTCODE => 75477,
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_CITY => 'CityM',
            AddressInterface::KEY_LASTNAME => 'Smith',
            AddressInterface::KEY_FIRSTNAME => 'John',
            AddressInterface::KEY_CUSTOMER_ID => 1,
            AddressInterface::CUSTOMER_ADDRESS_ID => 1,
            AddressInterface::KEY_REGION_ID => 1,
        ];
        $this->assertQuoteAddress($expectedBillingAddressData, $quote->getBillingAddress());
        $expectedShippingAddressData = [
            AddressInterface::CUSTOMER_ADDRESS_ID => 2,
            AddressInterface::KEY_TELEPHONE => 3234676,
            AddressInterface::KEY_POSTCODE => 47676,
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_CITY => 'CityX',
            AddressInterface::KEY_STREET => 'Black str, 48',
            AddressInterface::KEY_LASTNAME => 'Smith',
            AddressInterface::KEY_FIRSTNAME => 'John',
            AddressInterface::KEY_CUSTOMER_ID => 1,
            AddressInterface::KEY_REGION_ID => 1,
        ];
        $this->assertQuoteAddress($expectedShippingAddressData, $quote->getShippingAddress());
    }

    /**
     * Billing and shipping address arguments are passed, customer has default billing and shipping addresses.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     * @return void
     */
    public function testAssignCustomerWithAddressChange(): void
    {
        $quote = $this->quoteFactory->create();
        $customerData = $this->prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        $expectedBillingAddressData = [
            AddressInterface::KEY_STREET => 'Billing str, 67',
            AddressInterface::KEY_TELEPHONE => 16546757,
            AddressInterface::KEY_POSTCODE => 2425457,
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_CITY => 'CityBilling',
            AddressInterface::KEY_LASTNAME => 'LastBilling',
            AddressInterface::KEY_FIRSTNAME => 'FirstBilling',
            AddressInterface::KEY_REGION_ID => 1,
        ];
        $quoteBillingAddress = $this->addressFactory->create();
        $quoteBillingAddress->setData($expectedBillingAddressData);
        $expectedShippingAddressData = [
            AddressInterface::KEY_TELEPHONE => 787878787,
            AddressInterface::KEY_POSTCODE => 117785,
            AddressInterface::KEY_COUNTRY_ID => 'US',
            AddressInterface::KEY_CITY => 'CityShipping',
            AddressInterface::KEY_STREET => 'Shipping str, 48',
            AddressInterface::KEY_LASTNAME => 'LastShipping',
            AddressInterface::KEY_FIRSTNAME => 'FirstShipping',
            AddressInterface::KEY_REGION_ID => 1,
        ];
        $quoteShippingAddress = $this->addressFactory->create();
        $quoteShippingAddress->setData($expectedShippingAddressData);
        $quote->assignCustomerWithAddressChange($customerData, $quoteBillingAddress, $quoteShippingAddress);
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');
        $this->assertQuoteAddress($expectedBillingAddressData, $quote->getBillingAddress());
        $this->assertQuoteAddress($expectedShippingAddressData, $quote->getShippingAddress());
    }

    /**
     * Customer has address with country which not allowed in website
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Backend/_files/allowed_countries_fr.php
     * @return void
     */
    public function testAssignCustomerWithAddressChangeWithNotAllowedCountry(): void
    {
        $quote = $this->quoteFactory->create();
        $customerData = $this->prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        $quote->assignCustomerWithAddressChange($customerData);
        $this->assertNull($quote->getBillingAddress()->getCountryId());
        $this->assertNull($quote->getShippingAddress()->getCountryId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testAddProductUpdateItem(): void
    {
        $quote = $this->quoteFactory->create();
        $productStockQty = 100;
        $product = $this->productRepository->get('simple-1', false, null, true);
        $quote->addProduct($product, 50);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->assertEquals(50, $quote->getItemsQty());
        $quote->addProduct($product, 50);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->assertEquals(100, $quote->getItemsQty());
        $params = [
            'related_product' => '',
            'product' => $product->getId(),
            'qty' => 1,
            'id' => 0,
        ];
        $updateParams = new \Magento\Framework\DataObject($params);
        $quote->updateItem($updateParams['id'], $updateParams);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->assertEquals(1, $quote->getItemsQty());
        $this->expectException(LocalizedException::class);
        // TODO: fix test or implementation as described in https://github.com/magento-engcom/msi/issues/1037
//        $this->expectExceptionMessage('The requested qty is not available');
        $updateParams['qty'] = $productStockQty + 1;
        $quote->updateItem($updateParams['id'], $updateParams);
    }

    /**
     * Test to verify that reserved_order_id will be changed if it already in used
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDataFixture Magento/Quote/_files/empty_quote.php
     * @return void
     */
    public function testReserveOrderId(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('reserved_order_id');
        $quote->reserveOrderId();
        $this->assertEquals('reserved_order_id', $quote->getReservedOrderId());
        $quote->setReservedOrderId('100000001');
        $quote->reserveOrderId();
        $this->assertNotEquals('100000001', $quote->getReservedOrderId());
    }

    /**
     * Test to verify that disabled product cannot be added to cart
     * @magentoDataFixture Magento/Quote/_files/is_not_salable_product.php
     * @return void
     */
    public function testAddedProductToQuoteIsSalable(): void
    {
        $product = $this->productRepository->getById(99, false, null, true);
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage((string)__('Product that you are trying to add is not available.'));
        $this->quoteFactory->create()->addProduct($product);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testGetItemById(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('test01');
        $quoteItem = $this->itemFactory->create();
        $product = $this->productRepository->get('simple');
        $quoteItem->setProduct($product);
        $quote->addItem($quoteItem);
        $quote->save();
        $item = $quote->getItemById($quoteItem->getId());
        $this->assertInstanceOf(CartItemInterface::class, $item);
        $this->assertEquals($quoteItem->getId(), $item->getId());
    }

    /**
     * Tests of quotes merging.
     *
     * @param int|null $guestItemGiftMessageId
     * @param int|null $customerItemGiftMessageId
     * @param int|null $guestOrderGiftMessageId
     * @param int|null $customerOrderGiftMessageId
     * @param int|null $expectedItemGiftMessageId
     * @param int|null $expectedOrderGiftMessageId
     *
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @dataProvider giftMessageDataProvider
     * @return void
     */
    public function testMerge(
        ?int $guestItemGiftMessageId,
        ?int $customerItemGiftMessageId,
        ?int $guestOrderGiftMessageId,
        ?int $customerOrderGiftMessageId,
        ?int $expectedItemGiftMessageId,
        ?int $expectedOrderGiftMessageId
    ): void {
        $product = $this->productRepository->get('simple', false, null, true);
        $guestQuote = $this->getQuoteByReservedOrderId->execute('test01');
        $guestQuote->setGiftMessageId($guestOrderGiftMessageId);
        $customerQuote = $this->quoteFactory->create();
        $customerQuote->setReservedOrderId('test02')
            ->setStoreId($guestQuote->getStoreId())
            ->addProduct($product);
        $customerQuote->setGiftMessageId($customerOrderGiftMessageId);
        $guestItem = $guestQuote->getItemByProduct($product);
        $guestItem->setGiftMessageId($guestItemGiftMessageId);
        $customerItem = $customerQuote->getItemByProduct($product);
        $customerItem->setGiftMessageId($customerItemGiftMessageId);
        $customerQuote->merge($guestQuote);
        $mergedItemItem = $customerQuote->getItemByProduct($product);
        $this->assertEquals($expectedOrderGiftMessageId, $customerQuote->getGiftMessageId());
        $this->assertEquals($expectedItemGiftMessageId, $mergedItemItem->getGiftMessageId());
    }

    /**
     * Provides order- and item-level gift message Id.
     *
     * @return array
     */
    public static function giftMessageDataProvider(): array
    {
        return [
            [
                'guestItemGiftMessageId' => null,
                'customerItemGiftMessageId' => 1,
                'guestOrderGiftMessageId' => null,
                'customerOrderGiftMessageId' => 11,
                'expectedItemGiftMessageId' => 1,
                'expectedOrderGiftMessageId' => 11,
            ],
            [
                'guestItemGiftMessageId' => 1,
                'customerItemGiftMessageId' => 2,
                'guestOrderGiftMessageId' => 11,
                'customerOrderGiftMessageId' => 22,
                'expectedItemGiftMessageId' => 1,
                'expectedOrderGiftMessageId' => 11,
            ],
        ];
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_custom_file_option.php
     *
     * @return void
     */
    public function testAddProductWithoutChosenOptions(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple_with_custom_file_option');
        $result = $quote->addProduct($product);
        $this->assertEquals(
            (string)__(
                'The product\'s required option(s) weren\'t entered. Make sure the options are entered and try again.'
            ),
            $result
        );
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithInvalidRequestParams(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-1');
        $this->expectExceptionObject(
            new LocalizedException(__('We found an invalid request for adding product to quote.'))
        );
        $quote->addProduct($product, '');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     *
     * @return void
     */
    public function testAddProductOutOfStock(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-out-of-stock');
        $this->expectExceptionObject(
            new LocalizedException(__('Product that you are trying to add is not available.'))
        );
        $quote->addProduct($product, 1);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     *
     * @return void
     */
    public function testAddProductWithMoreQty(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-1');
        $this->expectExceptionObject(new LocalizedException(__('Not enough items for sale')));
        $quote->addProduct($product, 1500);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_with_qty_increments.php
     *
     * @return void
     */
    public function testAddProductWithQtyIncrements(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple_product_with_qty_increments');
        $this->expectExceptionObject(
            new LocalizedException(__('You can buy this product only in quantities of %1 at a time.', 3))
        );
        $quote->addProduct($product, 1);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_min_max_sale_qty.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testAddProductWithMinSaleQty(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple_product_min_max_sale_qty');
        $messages = [
            (string)__('The fewest you may purchase is %1.', 5),
            (string)__('The fewest you may purchase is %1', 5),
        ];
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/' . implode('|', $messages) . '/');
        $quote->addProduct($product, 1);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/simple_product_min_max_sale_qty.php
     *
     * @return void
     */
    public function testAddProductWithMaxSaleQty(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple_product_min_max_sale_qty');
        $messages = [
            (string)__('The most you may purchase is %1.', 20),
            (string)__('The requested qty exceeds the maximum qty allowed in shopping cart'),
        ];
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/' . implode('|', $messages) . '/');
        $quote->addProduct($product, 25);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/enable_qty_increments 1
     * @magentoConfigFixture current_store cataloginventory/item_options/qty_increments 3
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testAddProductWithConfigQtyIncrements(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-1');
        $this->expectExceptionObject(
            new LocalizedException(__('You can buy this product only in quantities of %1 at a time.', 3))
        );
        $quote->addProduct($product, 1);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/min_sale_qty 5
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testAddProductWithConfigMinSaleQty(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-1');
        $messages = [
            (string)__('The fewest you may purchase is %1.', 5),
            (string)__('The fewest you may purchase is %1', 5),
        ];
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/' . implode('|', $messages) . '/');
        $quote->addProduct($product, 1);
    }

    /**
     * @magentoConfigFixture current_store cataloginventory/item_options/max_sale_qty 20
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @magentoAppIsolation enabled
     *
     * @return void
     */
    public function testAddProductWithConfigMaxSaleQty(): void
    {
        $quote = $this->quoteFactory->create();
        $product = $this->productRepository->get('simple-1');
        $messages = [
            (string)__('The most you may purchase is %1.', 20),
            (string)__('The requested qty exceeds the maximum qty allowed in shopping cart'),
        ];
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessageMatches('/' . implode('|', $messages) . '/');
        $quote->addProduct($product, 25);
    }

    /**
     * Assert address in quote.
     *
     * @param array $expectedAddress
     * @param AddressInterface $quoteAddress
     * @return void
     */
    private function assertQuoteAddress(array $expectedAddress, AddressInterface $quoteAddress): void
    {
        foreach ($expectedAddress as $field => $value) {
            $this->assertEquals(
                $value,
                $quoteAddress->getData($field),
                sprintf('"%s" value in quote %s address is invalid.', $field, $quoteAddress->getAddressType())
            );
        }
    }

    /**
     * Prepare quote for testing assignCustomerWithAddressChange method.
     * Customer with two addresses created. First address is default billing, second is default shipping.
     *
     * @param CartInterface $quote
     * @return CustomerInterface
     */
    private function prepareQuoteForTestAssignCustomerWithAddressChange(CartInterface $quote): CustomerInterface
    {
        $fixtureCustomerId = 1;
        $fixtureSecondAddressId = 2;
        $customer = $this->customerFactory->create();
        $this->customerResourceModel->load($customer, $fixtureCustomerId);
        $customer->setDefaultShipping($fixtureSecondAddressId);
        $this->customerResourceModel->save($customer);
        $customerData = $customer->getDataModel();
        $this->assertEmpty(
            $quote->getBillingAddress()->getId(),
            "Precondition failed: billing address should be empty."
        );
        $this->assertEmpty(
            $quote->getShippingAddress()->getId(),
            "Precondition failed: shipping address should be empty."
        );

        return $customerData;
    }

    /**
     * @return array
     */
    private function getCustomerDataArray(): array
    {
        return [
            CustomerInterface::CONFIRMATION => 'test',
            CustomerInterface::CREATED_AT => '2/3/2014',
            CustomerInterface::CREATED_IN => 'Default',
            CustomerInterface::DEFAULT_BILLING => 'test',
            CustomerInterface::DEFAULT_SHIPPING => 'test',
            CustomerInterface::DOB => '2014-02-03 00:00:00',
            CustomerInterface::EMAIL => 'qa@example.com',
            CustomerInterface::FIRSTNAME => 'Joe',
            CustomerInterface::GENDER => 0,
            CustomerInterface::GROUP_ID => GroupManagement::NOT_LOGGED_IN_ID,
            CustomerInterface::ID => 1,
            CustomerInterface::LASTNAME => 'Dou',
            CustomerInterface::MIDDLENAME => 'Ivan',
            CustomerInterface::PREFIX => 'Dr.',
            CustomerInterface::STORE_ID => 1,
            CustomerInterface::SUFFIX => 'Jr.',
            CustomerInterface::TAXVAT => 1,
            CustomerInterface::WEBSITE_ID => 1,
        ];
    }

    /**
     * @magentoConfigFixture current_store sales/minimum_order/active 1
     * @magentoConfigFixture current_store sales/minimum_order/amount 5
     * @magentoConfigFixture current_store sales/minimum_order/tax_including 1
     * @magentoConfigFixture current_store sales/minimum_order/include_discount_amount 1
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture current_store tax/calculation/apply_after_discount 1
     * @magentoConfigFixture current_store tax/calculation/cross_border_trade_enabled 1
     * @magentoDataFixture Magento/SalesRule/_files/cart_rule_with_coupon_5_off_no_condition.php
     * @magentoDataFixture Magento/Tax/_files/tax_rule_region_1_al.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_taxable_product_and_customer.php
     */
    public function testValidateMinimumAmountWithPriceInclTaxAndDiscount()
    {
        /** @var $quote \Magento\Quote\Model\Quote */
        $quote = $this->getQuoteByReservedOrderId->execute('test_order_with_taxable_product');
        $quote->setCouponCode('CART_FIXED_DISCOUNT_5');
        $quote->collectTotals();
        $this->assertEquals(-5, $quote->getShippingAddress()->getBaseDiscountAmount());
        $this->assertEquals(9.3, $quote->getShippingAddress()->getBaseSubtotal());
        $this->assertEquals(5, $quote->getShippingAddress()->getBaseGrandTotal());
        $this->assertTrue($quote->validateMinimumAmount());
    }

    /**
     * @magentoConfigFixture current_store multishipping/options/checkout_multiple 1
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Multishipping/Fixtures/quote_with_split_items.php
     * @return void
     */
    public function testIsMultiShippingModeEnabledAfterQuoteItemRemoved(): void
    {
        $quote = $this->getQuoteByReservedOrderId->execute('multishipping_quote_id');
        /** @var CheckoutSession $session */
        $session = $this->objectManager->get(CheckoutSession::class);
        $session->replaceQuote($quote);
        $items = $quote->getAllItems();
        $idToDelete = null;
        foreach ($items as $item) {
            if (!$item->getProduct()->isVirtual() && $item->getQty() == 1) {
                $idToDelete = $item->getId();
            }
        }

        if (!is_null($idToDelete)) {
            $quoteShippingAddresses = $quote->getAllShippingAddresses();
            foreach ($quoteShippingAddresses as $shippingAddress) {
                if ($shippingAddress->getItemById($idToDelete)) {
                    $shippingAddress->removeItem($idToDelete);
                    $shippingAddress->setCollectShippingRates(true);

                    if (count($shippingAddress->getAllItems()) == 0) {
                        $shippingAddress->isDeleted(true);
                    }
                }
            }
            $quote->removeItem($idToDelete);
            $this->assertEquals(
                1,
                $quote->getIsMultiShipping(),
                "Multi-shipping mode is disabled after quote item removal"
            );
        } else {
            $this->assertTrue(
                !is_null($idToDelete),
                "No Simple Product item with qty 1 to delete exists"
            );
        }
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 922903400.00], as: 'product'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$product.id$', 'qty' => 1]
        ),
    ]
    public function testQuoteItemWithPriceGreaterThan100Millions()
    {
        $product = $this->fixtures->get('product');
        $cart = $this->fixtures->get('cart');
        $item = $cart->getItemsCollection(false)->fetchItem();
        $this->assertEquals(
            round((float)$product->getPrice(), 2),
            round((float)$item->getPrice(), 2)
        );
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p', count: 2),
        DataFixture(AttributeFixture::class, as: 'attr'),
        DataFixture(
            ConfigurableProductFixture::class,
            ['_options' => ['$attr$'], '_links' => ['$p1$', '$p2$']],
            'cp1'
        ),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$cp1.id$', 'child_product_id' => '$p1.id$', 'qty' => 2],
        ),
        DataFixture(
            AddConfigurableProductToCartFixture::class,
            ['cart_id' => '$cart.id$', 'product_id' => '$cp1.id$', 'child_product_id' => '$p2.id$', 'qty' => 2],
        ),
        DataFixture('deleteProduct')
    ]
    public function testCollectTotalsWhenConfigurableChildIsDeleted(): void
    {
        $cart = $this->fixtures->get('cart');
        $cart = $this->cartRepository->get($cart->getId());
        $cart->setTotalsCollectedFlag(false)->collectTotals();
        $items = $cart->getAllItems();
        $this->assertCount(3, $items);
        $this->assertCount(0, $items[0]->getChildren());
        $this->assertTrue($items[0]->getHasError());
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 10], as: 'p', count: 2),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p1$']], 'opt1'),
        DataFixture(BundleOptionFixture::class, ['product_links' => ['$p2$']], 'opt2'),
        DataFixture(BundleProductFixture::class, ['_options' => ['$opt1$', '$opt2$']], 'bp1'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(
            AddBundleProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$bp1.id$',
                'selections' => [['$p1.id$'], ['$p2.id$']],
                'qty' => 1
            ],
        ),
        DataFixture('deleteProduct')
    ]
    public function testCollectTotalsWhenBundleChildIsDeleted(): void
    {
        $cart = $this->fixtures->get('cart');
        $cart = $this->cartRepository->get($cart->getId());
        $cart->setTotalsCollectedFlag(false)->collectTotals();
        $items = $cart->getAllItems();
        $this->assertCount(2, $items);
        $this->assertCount(1, $items[0]->getChildren());
        $this->assertTrue($items[0]->getHasError());
    }

    public static function deleteProduct(): void
    {
        $registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
        $isSecureArea = $registry->registry('isSecureArea');
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        try {
            ObjectManager::getInstance()->get(ProductRepositoryInterface::class)->deleteById(
                DataFixtureStorageManager::getStorage()->get('p1')->getSku()
            );
        } finally {
            $registry->unregister('isSecureArea');
            $registry->register('isSecureArea', $isSecureArea);
        }
    }
}
