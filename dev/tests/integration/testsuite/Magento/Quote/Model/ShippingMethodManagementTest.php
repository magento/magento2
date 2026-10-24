<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Virtual as VirtualProductFixture;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Vat;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\GuestShippingMethodManagementInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Observer\Frontend\Quote\Address\CollectTotalsObserver;
use Magento\Quote\Test\Fixture\AddProductToCart as AddProductToCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Checkout\Test\Fixture\SetBillingAddress as SetBillingAddressFixture;
use Magento\Checkout\Test\Fixture\SetShippingAddress as SetShippingAddressFixture;
use Magento\SalesRule\Test\Fixture\AddressCondition as AddressConditionFixture;
use Magento\SalesRule\Test\Fixture\Rule as RuleFixture;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Api\Data\TaxClassInterface;
use Magento\Tax\Api\TaxClassRepositoryInterface;
use Magento\Tax\Model\ClassModel;
use Magento\Tax\Model\Config as TaxConfig;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Quote\Model\GetQuoteByReservedOrderId;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\OfflineShipping\Test\Fixture\TablerateFixture as TablerateFixture;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Customer\Test\Fixture\CustomerGroup as CustomerGroupFixture;
use Magento\Tax\Test\Fixture\TaxRate;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Tax\Test\Fixture\CustomerTaxClass;
use Magento\Tax\Test\Fixture\ProductTaxClass;

/**
 * Test for shipping methods management
 *
 * @magentoDbIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagementTest extends TestCase
{
    /** @var ObjectManagerInterface $objectManager */
    private $objectManager;

    /** @var GroupRepositoryInterface $groupRepository */
    private $groupRepository;

    /** @var TaxClassRepositoryInterface $taxClassRepository */
    private $taxClassRepository;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->groupRepository = $this->objectManager->get(GroupRepositoryInterface::class);
        $this->taxClassRepository = $this->objectManager->get(TaxClassRepositoryInterface::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates_price.php
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/active', '0', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_value_with_discount', 'store'),
        ConfigFixture('carriers/tablerate/include_virtual_price', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, ['sku' => 'simple', 'special_price' => 5.99], 'p1'),
        DataFixture(VirtualProductFixture::class, ['sku' => 'virtual', 'weight' => 0], 'p2'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p2.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$cart.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$cart.id$']),
    ]
    public function testTableRateWithoutIncludingVirtualProduct()
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();

        if (!$cartId) {
            $this->fail('quote fixture failed');
        }

        /** @var QuoteRepository $quoteRepository */
        $quoteRepository = $this->objectManager->get(QuoteRepository::class);
        $quote = $quoteRepository->get($cartId);

        /** @var QuoteIdToMaskedQuoteIdInterface $maskedQuoteId */
        $maskedQuoteId = $this->objectManager->get(QuoteIdToMaskedQuoteIdInterface::class)->execute($cartId);

        /** @var GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $this->objectManager->get(GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByExtendedAddress(
            $maskedQuoteId,
            $quote->getShippingAddress()
        );

        $this->assertCount(1, $result);
        $rate = reset($result);
        $expectedResult = [
            'method_code' => 'bestway',
            'amount' => 15,
        ];
        $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        $this->assertEquals($expectedResult['amount'], $rate->getAmount());
    }

    /**
     * Test table rate amount for the cart that contains some items with free shipping applied.
     * @magentoDbIsolation enabled
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/active', '0', 'store', 'default'),
        ConfigFixture('carriers/freeshipping/active', '0', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_value_with_discount', 'store'),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 1',
                'parent_id' => 2
            ],
            'cat3'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 2',
                'parent_id' => 2
            ],
            'cat6'
        ),

        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-tablerate-1',
                'price' => 30,
                'category_ids' => ['$cat3.id$']
            ],
            'p1'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-tablerate-2',
                'price' => 40,
                'category_ids' => ['$cat6.id$']
            ],
            'p2'
        ),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-tablerate-3',
                'price' => 50,
                'category_ids' => ['$cat6.id$']
            ],
            'p3'
        ),

        DataFixture(
            GuestCartFixture::class,
            [
                'reserved_order_id' => 'tableRate'
            ],
            'cart'
        ),

        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$p1.id$'
            ]
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$p2.id$'
            ]
        ),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$cart.id$',
                'product_id' => '$p3.id$'
            ]
        ),

        DataFixture(
            SetBillingAddressFixture::class,
            [
                'cart_id' => '$cart.id$'
            ]
        ),
        DataFixture(
            SetShippingAddressFixture::class,
            [
                'cart_id' => '$cart.id$'
            ]
        ),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing' => 0,
                'simple_free_shipping' => 1,
                'apply_to_shipping' => 1,
                'actions' => [
                    [
                        'attribute' => 'category_ids',
                        'operator' => '()',
                        'value' => '$cat3.id$'
                    ]
                ]
            ]
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 0.00,
                'price' => 15
            ]
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 50.00,
                'price' => 10
            ]
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 100.00,
                'price' => 5
            ]
        )
    ]
    public function testTableRateWithCartRuleForFreeShipping()
    {
        $objectManager = Bootstrap::getObjectManager();
        $quote = $this->getQuote('tableRate');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        /** @var  GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByExtendedAddress($cartId, $address);
        $this->assertCount(1, $result);
        $rate = reset($result);
        $expectedResult = [
            'method_code' => 'bestway',
            'amount' => 5
        ];
        $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        $this->assertEquals($expectedResult['amount'], $rate->getAmount());
    }

    /**
     * Verify 100% off rule applied to shipping and items using new fixtures
     * @return void
     * @throws NoSuchEntityException
     */
    #[
        DataFixture(
            RuleFixture::class,
            [
                'discount_amount' => 100,
                'simple_action' => 'by_percent',
                'apply_to_shipping' => 1,
                'simple_free_shipping' => 1
            ]
        ),
        DataFixture(ProductFixture::class, as: 'prod'),
        DataFixture(CustomerFixture::class, ['email' => 'customer@example.com'], 'customer'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'ccart'),
        DataFixture(
            AddProductToCartFixture::class,
            [
                'cart_id' => '$ccart.id$',
                'product_id' => '$prod.id$'
            ]
        ),
    ]
    public function testRateAppliedToShipping(): void
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $objectManager->create(CartRepositoryInterface::class);
        $customerId = (int)$this->fixtures->get('customer')->getId();
        $customerQuote = $quoteRepository->getForCustomer($customerId);
        $this->assertEquals(0, $customerQuote->getBaseGrandTotal());
    }

    /**
     * Table rate free shipping using new fixtures
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/active', '0', 'store', 'default'),
        ConfigFixture('carriers/freeshipping/active', '0', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty', 'store'),
        DataFixture(ProductFixture::class, ['sku' => 'free_item', 'price' => 7], as: 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test01'], 'q1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$q1.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$q1.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$q1.id$']),
        DataFixture(RuleFixture::class, [
            'stop_rules_processing' => 0,
            'simple_free_shipping' => 1,
            'apply_to_shipping' => 1,
            'actions' => [
                [
                    'attribute' => 'quote_item_price',
                    'operator' => '==',
                    'value' => '7'
                ]
            ]
        ]),
        DataFixture(TablerateFixture::class, ['price' => 0, 'condition_name' => 'package_qty', 'condition_value' => 1]),
    ]
    public function testTableRateFreeShipping()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Quote $quote */
        $quote = $objectManager->get(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $data = [
            'data' => [
                'country_id' => "US",
                'postcode' => null,
                'region' => null,
                'region_id' => null
            ]
        ];
        /** @var EstimateAddressInterface $address */
        $address = $objectManager->create(EstimateAddressInterface::class, $data);
        /** @var  GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddress($cartId, $address);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'method_code' => 'bestway',
            'amount' => 0
        ];
        foreach ($result as $rate) {
            $this->assertEquals($expectedResult['amount'], $rate->getAmount());
            $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        }
    }

    /**
     * Retrieves quote by reserved order id.
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

    /**
     * Estimate by address with cart price rule by item using new fixtures
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty', 'store'),
        ConfigFixture('carriers/flatrate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/price', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, ['sku' => 'free_item', 'price' => 7], as: 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test01'], 'q2'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$q2.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$q2.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$q2.id$']),
        DataFixture(TablerateFixture::class, ['price' => 0]),
        DataFixture(
            RuleFixture::class,
            [
                'stop_rules_processing' => 0,
                'simple_free_shipping' => 1,
                'apply_to_shipping' => 1,
                'actions' => [
                    [
                        'attribute' => 'quote_item_price',
                        'operator' => '==',
                        'value' => '7'
                    ]
                ]
            ]
        ),
    ]
    public function testEstimateByAddressWithCartPriceRuleByItem()
    {
        $this->executeTestFlow(0, 0);
    }

    /**
     * Estimate by address with cart price rule by shipment using new fixtures
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty', 'store'),
        ConfigFixture('carriers/flatrate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/price', '0', 'store', 'default'),
        DataFixture(ProductFixture::class, ['sku' => 'free_item', 'price' => 7], as: 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test01'], 'q3'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$q3.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$q3.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$q3.id$']),
        DataFixture(TablerateFixture::class),
        DataFixture(RuleFixture::class, [
            'stop_rules_processing' => 0,
            'simple_free_shipping' => 1,
            'apply_to_shipping' => 1,
            'actions' => [
                [
                    'attribute' => 'quote_item_price',
                    'operator' => '==',
                    'value' => '7'
                ]
            ]
        ]),
    ]
    public function testEstimateByAddressWithCartPriceRuleByShipment()
    {
        $this->markTestSkipped('According to MAGETWO-69940 it is an incorrect behavior');
        // Rule applied to entire shipment should not overwrite flat or table rate shipping prices
        // Only rules applied to specific items should modify those prices (MAGETWO-63844)
        $this->executeTestFlow(5, 10);
    }

    /**
     * Estimate by address using new fixtures
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_qty', 'store'),
        DataFixture(ProductFixture::class, as: 'product'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'test01'], 'q4'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$q4.id$', 'product_id' => '$product.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$q4.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$q4.id$']),
        DataFixture(TablerateFixture::class, ['price' => 10]),
    ]
    public function testEstimateByAddress()
    {
        $this->executeTestFlow(5, 10);
    }

    /**
     * Provide testing of shipping method estimation based on address
     *
     * @param int $flatRateAmount
     * @param int $tableRateAmount
     * @return void
     */
    private function executeTestFlow($flatRateAmount, $tableRateAmount)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var Quote $quote */
        $quote = $objectManager->get(Quote::class);
        $quote->load('test01', 'reserved_order_id');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $data = [
            'data' => [
                'country_id' => "US",
                'postcode' => null,
                'region' => null,
                'region_id' => null
            ]
        ];
        /** @var EstimateAddressInterface $address */
        $address = $objectManager->create(EstimateAddressInterface::class, $data);
        /** @var  GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddress($cartId, $address);
        $this->assertNotEmpty($result);
        $expectedResult = [
            'tablerate' => [
                'method_code' => 'bestway',
                'amount' => $tableRateAmount
            ],
            'flatrate' => [
                'method_code' => 'flatrate',
                'amount' => $flatRateAmount
            ]
        ];
        foreach ($result as $rate) {
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['amount'], $rate->getAmount());
            $this->assertEquals($expectedResult[$rate->getCarrierCode()]['method_code'], $rate->getMethodCode());
        }
    }

    /**
     * Test for estimate shipping with tax and changed VAT customer group
     *
     * @magentoDbIsolation disabled
     */
    #[
        // Shipping: only Flat Rate, base/net price 5.00
        ConfigFixture('carriers/flatrate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/price', '5', 'store', 'default'),
        ConfigFixture('carriers/tablerate/active', '0', 'store', 'default'),
        ConfigFixture('carriers/freeshipping/active', '0', 'store', 'default'),

        // Tax calculation: compute shipping tax from shipping address, shipping price is excl tax
        ConfigFixture('tax/calculation/based_on', 'shipping', 'store', 'default'),
        ConfigFixture('tax/calculation/price_includes_tax', '1', 'store', 'default'),
        ConfigFixture('tax/calculation/shipping_includes_tax', '0', 'store', 'default'),

        // Tax classes, rate and rule (21% for DE)
        DataFixture(CustomerTaxClass::class, ['class_name' => 'CustomerTaxClass'], 'customer_tax_class'),
        DataFixture(ProductTaxClass::class, ['class_name' => 'ProductTaxClass'], 'product_tax_class'),
        DataFixture(TaxRate::class, [
            'tax_country_id' => 'DE',
            'tax_region_id' => 0,
            'tax_postcode' => '*',
            'code' => 'Denmark',
            'rate' => 21
        ], 'tax_rate'),
        DataFixture(TaxRule::class, [
            'customer_tax_class_ids' => ['$customer_tax_class.id$'],
            'product_tax_class_ids' => ['$product_tax_class.id$'],
            'tax_rate_ids' => ['$tax_rate.id$'],
            'priority' => 0,
            'code' => 'Test Rule'
        ], 'tax_rule'),

        // Customer group + customer with DE default shipping address
        DataFixture(CustomerGroupFixture::class, [GroupInterface::CODE => 'custom_group'], 'group'),
        DataFixture(CustomerFixture::class, [
            'email' => 'customer@example.com',
            'group_id' => '$group.id$',
            'addresses' => [[
                'country_id' => 'DE',
                'region_id' => 0,
                'city' => 'Berlin',
                'street' => ['1059 George Avenue'],
                'postcode' => '10178',
                'telephone' => '1234567890',
                'default_billing' => true,
                'default_shipping' => true
            ]]
        ], 'customer'),

        // Customer cart with reserved order id and one physical item
        DataFixture(
            CustomerCartFixture::class,
            [
                'customer_id' => '$customer.id$',
                'reserved_order_id' => 'test01'
            ],
            'cart'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'vat-simple', 'price' => 10], 'p1'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$cart.id$', 'product_id' => '$p1.id$']),
    ]
    public function testEstimateByAddressWithInclExclTaxAndVATGroup()
    {
        /** @var GroupInterface $customerGroup */
        $customerGroup = $this->findCustomerGroupByCode('custom_group');
        $this->mockCustomerVat((int)$customerGroup->getId());

        $customerGroup->setTaxClassId($this->getTaxClass('CustomerTaxClass')->getClassId());
        $this->groupRepository->save($customerGroup);

        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $customer->setGroupId($customerGroup->getId());
        $customer->setTaxvat('12');
        $customerRepository->save($customer);

        // Set shipping tax class to ProductTaxClass
        $this->setConfig($customerGroup->getId(), $this->getTaxClass('ProductTaxClass')->getClassId());
        // Ensure address is DE for 21% VAT
        $this->changeCustomerAddress($customer->getDefaultShipping());

        $quote = $this->objectManager->get(GetQuoteByReservedOrderId::class)->execute('test01');
        // Ensure quote uses the updated group (so customer tax class applies)
        $quote->setCustomerGroupId((int)$customerGroup->getId())->save();

        $addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $address = $addressRepository->getById((int)$customer->getDefaultShipping());
        $address->setIsDefaultShipping(true);
        $customer->setAddresses([$address]);

        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->loginById($customer->getId());

        /** @var ShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $this->objectManager->get(ShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByAddressId($quote->getId(), (int)$customer->getDefaultShipping());

        $this->assertEquals(6.05, $result[0]->getPriceInclTax());
        $this->assertEquals(5.0, $result[0]->getPriceExclTax());
    }

    /**
     * Create a test double fot customer vat class
     *
     * @param int $customerGroupId
     */
    private function mockCustomerVat(int $customerGroupId): void
    {
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'request_date' => '',
            'request_identifier' => '123123123',
            'request_success' => false,
            'request_message' => __('Error during VAT Number verification.'),
        ]);
        $customerVat = $this->createPartialMock(
            Vat::class,
            [
                'checkVatNumber',
                'isCountryInEU',
                'getCustomerGroupIdBasedOnVatNumber',
                'getMerchantCountryCode',
                'getMerchantVatNumber'
            ]
        );
        $customerVat->method('checkVatNumber')->willReturn($gatewayResponse);
        $customerVat->method('isCountryInEU')->willReturn(true);
        $customerVat->method('getMerchantCountryCode')->willReturn('GB');
        $customerVat->method('getMerchantVatNumber')->willReturn('11111');
        $customerVat->method('getCustomerGroupIdBasedOnVatNumber')->willReturn($customerGroupId);
        $this->objectManager->removeSharedInstance(Vat::class);
        $this->objectManager->addSharedInstance($customerVat, Vat::class);

        // Remove instances where the customer vat object is cached
        $this->objectManager->removeSharedInstance(CollectTotalsObserver::class);
    }

    /**
     * Find the group with a given code.
     *
     * @param string $code
     * @return GroupInterface
     */
    protected function findCustomerGroupByCode(string $code): ?GroupInterface
    {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter('code', $code)
            ->create();
        $groups = $this->groupRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($groups);
    }

    /**
     * Change customer address
     *
     * @param int $customerAddressId
     *
     * @return AddressInterface
     */
    private function changeCustomerAddress(int $customerAddressId): AddressInterface
    {
        $addressRepository = $this->objectManager->get(AddressRepositoryInterface::class);
        $address = $addressRepository->getById($customerAddressId);
        $address->setVatId(12345);
        $address->setCountryId('DE');
        $address->setRegionId(0);
        $address->setPostcode(10178);

        return $addressRepository->save($address);
    }

    /**
     * Get tax class.
     *
     * @param string $name
     *
     * @return TaxClassInterface
     */
    private function getTaxClass(string $name): ?TaxClassInterface
    {
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchBuilder->addFilter(ClassModel::KEY_NAME, $name)
            ->create();
        $searchResults = $this->taxClassRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($searchResults);
    }

    /**
     * Set the configuration.
     *
     * @param int $customerGroupId
     * @param int $productTaxClassId
     *
     * @return void
     */
    private function setConfig(int $customerGroupId, int $productTaxClassId): void
    {
        $configData = [
            [
                'path' => Vat::XML_PATH_CUSTOMER_VIV_INVALID_GROUP,
                'value' => $customerGroupId,
                'scope' => ScopeInterface::SCOPE_STORE,
            ],
            [
                'path' => TaxConfig::CONFIG_XML_PATH_SHIPPING_TAX_CLASS,
                'value' => $productTaxClassId,
                'scope' => ScopeInterface::SCOPE_STORE,
            ],
        ];
        $config = $this->objectManager->get(MutableScopeConfigInterface::class);
        foreach ($configData as $data) {
            $config->setValue($data['path'], $data['value'], $data['scope']);
        }
    }

    /**
     *
     * Test table rate with zero amount is available for the cart when discount coupon cart price rule to all items
     * and freeshipping cart price rule is applied when order subtotal is greater than specified amount.
     *
     * @return void
     */
    #[
        ConfigFixture('carriers/tablerate/active', '1', 'store', 'default'),
        ConfigFixture('carriers/flatrate/active', '0', 'store', 'default'),
        ConfigFixture('carriers/freeshipping/active', '0', 'store', 'default'),
        ConfigFixture('carriers/tablerate/condition_name', 'package_value_with_discount', 'store'),
        DataFixture(ProductFixture::class, ['sku' => 'tableRateP1', 'price' => 30], 'tp1'),
        DataFixture(ProductFixture::class, ['sku' => 'tableRateP2', 'price' => 40], 'tp2'),
        DataFixture(ProductFixture::class, ['sku' => 'tableRateP3', 'price' => 50], 'tp3'),
        DataFixture(GuestCartFixture::class, ['reserved_order_id' => 'tableRate'], 'tquote'),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$tquote.id$', 'product_id' => '$tp1.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$tquote.id$', 'product_id' => '$tp2.id$']),
        DataFixture(AddProductToCartFixture::class, ['cart_id' => '$tquote.id$', 'product_id' => '$tp3.id$']),
        DataFixture(SetBillingAddressFixture::class, ['cart_id' => '$tquote.id$']),
        DataFixture(SetShippingAddressFixture::class, ['cart_id' => '$tquote.id$']),
        DataFixture(
            AddressConditionFixture::class,
            ['attribute' => 'base_subtotal', 'operator' => '>=', 'value' => 30],
            'c1'
        ),
        DataFixture(
            RuleFixture::class,
            ['stop_rules_processing' => 0, 'simple_free_shipping' => 1, 'conditions' => ['$c1$']],
            'r_free_ship'
        ),
        DataFixture(
            RuleFixture::class,
            ['stop_rules_processing' => 0, 'coupon_code' => '123', 'discount_amount' => 20],
            'r_discount'
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 0.00,
                'price' => 15
            ]
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 50.00,
                'price' => 10
            ]
        ),
        DataFixture(
            TablerateFixture::class,
            [
                'condition_name' => 'package_value_with_discount',
                'condition_value' => 100.00,
                'price' => 5
            ]
        ),
    ]
    public function testTableRateWithZeroPriceShownWhenDiscountCouponAndFreeShippingCartRuleApplied()
    {
        $objectManager = Bootstrap::getObjectManager();
        $quote = $this->getQuote('tableRate');
        $cartId = $quote->getId();
        if (!$cartId) {
            $this->fail('quote fixture failed');
        }
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = Bootstrap::getObjectManager()
            ->create(QuoteIdMaskFactory::class)
            ->create();
        $quoteIdMask->load($cartId, 'quote_id');
        //Use masked cart Id
        $cartId = $quoteIdMask->getMaskedId();
        $addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $addressFactory->create();
        $address->setCountryId('US');
        /** @var CouponManagementInterface $couponManagement */
        $couponManagement = Bootstrap::getObjectManager()->get(CouponManagementInterface::class);
        $couponManagement->set($quote->getId(), '123');
        /** @var  GuestShippingMethodManagementInterface $shippingEstimation */
        $shippingEstimation = $objectManager->get(GuestShippingMethodManagementInterface::class);
        $result = $shippingEstimation->estimateByExtendedAddress($cartId, $address);
        $this->assertCount(1, $result);
        $rate = reset($result);

        $expectedResult = [
            'method_code' => 'bestway',
            'amount' => 0
        ];
        $this->assertEquals($expectedResult['method_code'], $rate->getMethodCode());
        $this->assertEquals($expectedResult['amount'], $rate->getAmount());
    }
}
