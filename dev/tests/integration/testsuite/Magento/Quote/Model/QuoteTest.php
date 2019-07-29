<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @param ExtensibleDataInterface $entity
     * @return array
     */
    private function convertToArray(ExtensibleDataInterface $entity): array
    {
        return $this->objectManager
            ->create(\Magento\Framework\Api\ExtensibleDataObjectConverter::class)
            ->toFlatArray($entity);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @return void
     */
    public function testCollectTotalsWithVirtual(): void
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');

        $productRepository = $this->objectManager->create(
            ProductRepositoryInterface::class
        );
        $product = $productRepository->get('virtual-product', false, null, true);
        $quote->addProduct($product);
        $quote->collectTotals();

        $this->assertEquals(2, $quote->getItemsQty());
        $this->assertEquals(1, $quote->getVirtualItemsQty());
        $this->assertEquals(20, $quote->getGrandTotal());
        $this->assertEquals(20, $quote->getBaseGrandTotal());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_virtual.php
     * @magentoDataFixture Magento/Quote/_files/empty_quote.php
     * @return void
     */
    public function testGetAddressWithVirtualProduct()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id_1', 'reserved_order_id');
        $billingAddress = $this->objectManager->create(AddressInterface::class);
        $billingAddress->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('US')
            ->setRegion('TX')
            ->setCity('Austin')
            ->setStreet('1000 West Parmer Line')
            ->setPostcode('11501')
            ->setTelephone('123456789');
        $quote->setBillingAddress($billingAddress);
        $shippingAddress = $this->objectManager->create(AddressInterface::class);
        $shippingAddress->setFirstname('Joe')
            ->setLastname('Doe')
            ->setCountryId('US')
            ->setRegion('NJ')
            ->setCity('Newark')
            ->setStreet('2775  Granville Lane')
            ->setPostcode('07102')
            ->setTelephone('9734685221');
        $quote->setShippingAddress($shippingAddress);
        $productRepository = $this->objectManager->create(
            ProductRepositoryInterface::class
        );
        $product = $productRepository->get('virtual-product', false, null, true);
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
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        /** @var CustomerInterfaceFactory $customerFactory */
        $customerFactory = $this->objectManager->create(
            CustomerInterfaceFactory::class
        );
        /** @var \Magento\Framework\Api\DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
        $expected = $this->_getCustomerDataArray();
        $customer = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $customer,
            $expected,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );

        $this->assertEquals($expected, $this->convertToArray($customer));
        $quote->setCustomer($customer);
        $customer = $quote->getCustomer();
        $this->assertEquals($expected, $this->convertToArray($customer));
        $this->assertEquals('qa@example.com', $quote->getCustomerEmail());
        $this->assertEquals('Joe', $quote->getCustomerFirstname());
        $this->assertEquals('Dou', $quote->getCustomerLastname());
        $this->assertEquals('Ivan', $quote->getCustomerMiddlename());
    }

    /**
     * @return void
     */
    public function testUpdateCustomerData(): void
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $customerFactory = $this->objectManager->create(
            CustomerInterfaceFactory::class
        );
        /** @var \Magento\Framework\Api\DataObjectHelper $dataObjectHelper */
        $dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
        $expected = $this->_getCustomerDataArray();
        //For save in repository
        $expected = $this->removeIdFromCustomerData($expected);
        $customerDataSet = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $customerDataSet,
            $expected,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->assertEquals($expected, $this->convertToArray($customerDataSet));
        /**
         * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
         */
        $customerRepository = $this->objectManager
            ->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $customerRepository->save($customerDataSet);
        $quote->setCustomer($customerDataSet);
        $expected = $this->_getCustomerDataArray();
        $expected = $this->changeEmailInCustomerData('test@example.com', $expected);
        $customerDataUpdated = $customerFactory->create();
        $dataObjectHelper->populateWithArray(
            $customerDataUpdated,
            $expected,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $quote->updateCustomerData($customerDataUpdated);
        $customer = $quote->getCustomer();
        $expected = $this->changeEmailInCustomerData('test@example.com', $expected);
        $actual = $this->convertToArray($customer);
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
        /** Preconditions */
        /** @var CustomerInterfaceFactory $customerFactory */
        $customerFactory = $this->objectManager->create(
            CustomerInterfaceFactory::class
        );
        $customerGroupId = 3;
        $customerData = $customerFactory->create()->setId(1)->setGroupId($customerGroupId);
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setCustomer($customerData);
        $quote->unsetData('customer_group_id');

        /** Execute SUT */
        $this->assertEquals($customerGroupId, $quote->getCustomerGroupId(), "Customer group ID is invalid");
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     * @return void
     */
    public function testGetCustomerTaxClassId(): void
    {
        /**
         * Preconditions: create quote and assign ID of customer group created in fixture to it.
         */
        $fixtureGroupCode = 'custom_group';
        $fixtureTaxClassId = 3;
        /** @var \Magento\Customer\Model\Group $group */
        $group = $this->objectManager->create(\Magento\Customer\Model\Group::class);
        $fixtureGroupId = $group->load($fixtureGroupCode, 'customer_group_code')->getId();
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->setCustomerGroupId($fixtureGroupId);

        /** Execute SUT */
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
        /** Preconditions:
         * Customer with two addresses created
         * First address is default billing, second is default shipping.
         */
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $customerData = $this->_prepareQuoteForTestAssignCustomerWithAddressChange($quote);

        /** Execute SUT */
        $quote->assignCustomerWithAddressChange($customerData);

        /** Check if SUT caused expected effects */
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');
        $expectedBillingAddressData = [
            'street' => 'Green str, 67',
            'telephone' => 3468676,
            'postcode' => 75477,
            'country_id' => 'US',
            'city' => 'CityM',
            'lastname' => 'Smith',
            'firstname' => 'John',
            'customer_id' => 1,
            'customer_address_id' => 1,
            'region_id' => 1
        ];
        $billingAddress = $quote->getBillingAddress();
        foreach ($expectedBillingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $billingAddress->getData($field),
                "'{$field}' value in quote billing address is invalid."
            );
        }
        $expectedShippingAddressData = [
            'customer_address_id' => 2,
            'telephone' => 3234676,
            'postcode' => 47676,
            'country_id' => 'US',
            'city' => 'CityX',
            'street' => 'Black str, 48',
            'lastname' => 'Smith',
            'firstname' => 'John',
            'customer_id' => 1,
            'region_id' => 1
        ];
        $shippingAddress = $quote->getShippingAddress();
        foreach ($expectedShippingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $shippingAddress->getData($field),
                "'{$field}' value in quote shipping address is invalid."
            );
        }
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
        /** Preconditions:
         * Customer with two addresses created
         * First address is default billing, second is default shipping.
         */
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $customerData = $this->_prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        /** @var \Magento\Quote\Model\Quote\Address $quoteBillingAddress */
        $expectedBillingAddressData = [
            'street' => 'Billing str, 67',
            'telephone' => 16546757,
            'postcode' => 2425457,
            'country_id' => 'US',
            'city' => 'CityBilling',
            'lastname' => 'LastBilling',
            'firstname' => 'FirstBilling',
            'region_id' => 1
        ];
        $quoteBillingAddress = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $quoteBillingAddress->setData($expectedBillingAddressData);

        $expectedShippingAddressData = [
            'telephone' => 787878787,
            'postcode' => 117785,
            'country_id' => 'US',
            'city' => 'CityShipping',
            'street' => 'Shipping str, 48',
            'lastname' => 'LastShipping',
            'firstname' => 'FirstShipping',
            'region_id' => 1
        ];
        $quoteShippingAddress = $this->objectManager->create(\Magento\Quote\Model\Quote\Address::class);
        $quoteShippingAddress->setData($expectedShippingAddressData);

        /** Execute SUT */
        $quote->assignCustomerWithAddressChange($customerData, $quoteBillingAddress, $quoteShippingAddress);

        /** Check if SUT caused expected effects */
        $fixtureCustomerId = 1;
        $this->assertEquals($fixtureCustomerId, $quote->getCustomerId(), 'Customer ID in quote is invalid.');

        $billingAddress = $quote->getBillingAddress();
        foreach ($expectedBillingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $billingAddress->getData($field),
                "'{$field}' value in quote billing address is invalid."
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        foreach ($expectedShippingAddressData as $field => $value) {
            $this->assertEquals(
                $value,
                $shippingAddress->getData($field),
                "'{$field}' value in quote shipping address is invalid."
            );
        }
    }

    /**
     * Customer has address with country which not allowed in website
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Backend/_files/allowed_countries_fr.php
     * @return void
     */
    public function testAssignCustomerWithAddressChangeWithNotAllowedCountry()
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $customerData = $this->_prepareQuoteForTestAssignCustomerWithAddressChange($quote);
        $quote->assignCustomerWithAddressChange($customerData);

        /** Check that addresses are empty */
        $this->assertNull($quote->getBillingAddress()->getCountryId());
        $this->assertNull($quote->getShippingAddress()->getCountryId());
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
     * @return void
     */
    public function testAddProductUpdateItem(): void
    {
        /** @var Quote $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');

        $productStockQty = 100;

        $productRepository = $this->objectManager->create(
            ProductRepositoryInterface::class
        );
        $product = $productRepository->get('simple-1', false, null, true);

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
            'id' => 0
        ];
        $updateParams = new \Magento\Framework\DataObject($params);
        $quote->updateItem($updateParams['id'], $updateParams);
        $quote->setTotalsCollectedFlag(false)->collectTotals();
        $this->assertEquals(1, $quote->getItemsQty());

        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        // TODO: fix test or implementation as described in https://github.com/magento-engcom/msi/issues/1037
        // $this->expectExceptionMessage('The requested qty is not available');
        $updateParams['qty'] = $productStockQty + 1;
        $quote->updateItem($updateParams['id'], $updateParams);
    }

    /**
     * Prepare quote for testing assignCustomerWithAddressChange method.
     *
     * Customer with two addresses created. First address is default billing, second is default shipping.
     *
     * @param Quote $quote
     * @return CustomerInterface
     */
    protected function _prepareQuoteForTestAssignCustomerWithAddressChange(Quote $quote): CustomerInterface
    {
        $customerRepository = $this->objectManager->create(
            CustomerRepositoryInterface::class
        );
        $fixtureCustomerId = 1;
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer = $this->objectManager->create(\Magento\Customer\Model\Customer::class);
        $fixtureSecondAddressId = 2;
        $customer->load($fixtureCustomerId)->setDefaultShipping($fixtureSecondAddressId)->save();
        $customerData = $customerRepository->getById($fixtureCustomerId);
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
     * @param string $email
     * @param array $customerData
     * @return array
     */
    protected function changeEmailInCustomerData(string $email, array $customerData): array
    {
        $customerData[\Magento\Customer\Model\Data\Customer::EMAIL] = $email;
        return $customerData;
    }

    /**
     * @param array $customerData
     * @return array
     */
    protected function removeIdFromCustomerData(array $customerData): array
    {
        unset($customerData[\Magento\Customer\Model\Data\Customer::ID]);
        return $customerData;
    }

    /**
     * @return array
     */
    protected function _getCustomerDataArray(): array
    {
        return [
            Customer::CONFIRMATION => 'test',
            Customer::CREATED_AT => '2/3/2014',
            Customer::CREATED_IN => 'Default',
            Customer::DEFAULT_BILLING => 'test',
            Customer::DEFAULT_SHIPPING => 'test',
            Customer::DOB => '2014-02-03 00:00:00',
            Customer::EMAIL => 'qa@example.com',
            Customer::FIRSTNAME => 'Joe',
            Customer::GENDER => 0,
            Customer::GROUP_ID => \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
            Customer::ID => 1,
            Customer::LASTNAME => 'Dou',
            Customer::MIDDLENAME => 'Ivan',
            Customer::PREFIX => 'Dr.',
            Customer::STORE_ID => 1,
            Customer::SUFFIX => 'Jr.',
            Customer::TAXVAT => 1,
            Customer::WEBSITE_ID => 1
        ];
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
        /** @var Quote  $quote */
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('reserved_order_id', 'reserved_order_id');
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
        $productId = 99;

        /** @var ProductRepository $productRepository */
        $productRepository = $this->objectManager->get(ProductRepository::class);

        /** @var Quote  $quote */
        $product = $productRepository->getById($productId, false, null, true);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Product that you are trying to add is not available.');

        $quote = $this->objectManager->create(Quote::class);
        $quote->addProduct($product);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/quote.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @return void
     */
    public function testGetItemById(): void
    {
        $quote = $this->objectManager->create(Quote::class);
        $quote->load('test01', 'reserved_order_id');

        $quoteItem = $this->objectManager->create(\Magento\Quote\Model\Quote\Item::class);

        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $quoteItem->setProduct($product);
        $quote->addItem($quoteItem);
        $quote->save();

        $this->assertInstanceOf(\Magento\Quote\Model\Quote\Item::class, $quote->getItemById($quoteItem->getId()));
        $this->assertEquals($quoteItem->getId(), $quote->getItemById($quoteItem->getId())->getId());
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
     * @throws LocalizedException
     * @return void
     */
    public function testMerge(
        $guestItemGiftMessageId,
        $customerItemGiftMessageId,
        $guestOrderGiftMessageId,
        $customerOrderGiftMessageId,
        $expectedItemGiftMessageId,
        $expectedOrderGiftMessageId
    ): void {
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple', false, null, true);

        /** @var Quote  $quote */
        $guestQuote = $this->getQuote('test01');
        $guestQuote->setGiftMessageId($guestOrderGiftMessageId);

        /** @var Quote  $customerQuote */
        $customerQuote = $this->objectManager->create(Quote::class);
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

        self::assertEquals($expectedOrderGiftMessageId, $customerQuote->getGiftMessageId());
        self::assertEquals($expectedItemGiftMessageId, $mergedItemItem->getGiftMessageId());
    }

    /**
     * Provides order- and item-level gift message Id.
     *
     * @return array
     */
    public function giftMessageDataProvider(): array
    {
        return [
            [
                'guestItemId' => null,
                'customerItemId' => 1,
                'guestOrderId' => null,
                'customerOrderId' => 11,
                'expectedItemId' => 1,
                'expectedOrderId' => 11
            ],
            [
                'guestItemId' => 1,
                'customerItemId' => 2,
                'guestOrderId' => 11,
                'customerOrderId' => 22,
                'expectedItemId' => 1,
                'expectedOrderId' => 11
            ]
        ];
    }

    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return Quote
     */
    private function getQuote(string $reservedOrderId): Quote
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();

        return array_pop($items);
    }
}
