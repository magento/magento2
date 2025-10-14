<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\CustomAttributeListInterface;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\Address\RateCollectorInterface;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;
use Magento\Quote\Model\ResourceModel\Quote\Address\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\Collection as RatesCollection;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory as RateCollectionFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for sales quote address model
 *
 * @see \Magento\Quote\Model\Quote\Address
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddressTest extends TestCase
{
    /**
     * @var Address
     */
    private $address;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var CustomAttributeListInterface|MockObject
     */
    private $attributeList;

    /**
     * @var Config|MockObject
     */
    private $scopeConfig;

    /**
     * @var RateRequestFactory|MockObject
     */
    private $requestFactory;

    /**
     * @var RateFactory|MockObject
     */
    private $addressRateFactory;

    /**
     * @var RateCollectionFactory|MockObject
     */
    private $rateCollectionFactory;

    /**
     * @var RateCollectorInterfaceFactory|MockObject
     */
    private $rateCollector;

    /**
     * @var RateCollectorInterface|MockObject
     */
    private $rateCollection;

    /**
     * @var CollectionFactory|MockObject
     */
    private $itemCollectionFactory;

    /**
     * @var RegionFactory|MockObject
     */
    private $regionFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface|MockObject
     */
    private $store;

    /**
     * @var WebsiteInterface|MockObject
     */
    private $website;

    /**
     * @var Region|MockObject
     */
    private $region;

    /**
     * @var Json|MockObject
     */
    protected $serializer;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->scopeConfig = $this->createMock(Config::class);
        $this->serializer = new Json();

        $this->requestFactory = $this->getMockBuilder(RateRequestFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressRateFactory = $this->getMockBuilder(RateFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rateCollector = $this->getMockBuilder(RateCollectorInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rateCollectionFactory = $this->getMockBuilder(RateCollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rateCollection = $this->getMockBuilder(RateCollectorInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getResult'])
            ->getMockForAbstractClass();

        $this->itemCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->regionFactory = $this->getMockBuilder(RegionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->region = $this->getMockBuilder(Region::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getBaseCurrency', 'getCurrentCurrency', 'getCurrentCurrencyCode'])
            ->getMockForAbstractClass();

        $this->website = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->attributeList = $this->createMock(
            CustomAttributeListInterface::class
        );
        $this->attributeList->method('getAttributes')->willReturn([]);

        $this->address = $objectManager->getObject(
            Address::class,
            [
                'attributeList' => $this->attributeList,
                'scopeConfig' => $this->scopeConfig,
                'serializer' => $this->serializer,
                'storeManager' => $this->storeManager,
                '_itemCollectionFactory' => $this->itemCollectionFactory,
                '_rateRequestFactory' => $this->requestFactory,
                '_rateCollectionFactory' => $this->rateCollectionFactory,
                '_rateCollector' => $this->rateCollector,
                '_regionFactory' => $this->regionFactory,
                '_addressRateFactory' => $this->addressRateFactory
            ]
        );
        $this->quote = $this->createMock(Quote::class);
        $this->address->setQuote($this->quote);
    }

    /**
     * @return void
     */
    public function testValidateMinimumAmountDisabled(): void
    {
        $storeId = 1;

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with('sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId)
            ->willReturn(false);

        $this->assertTrue($this->address->validateMinimumAmount());
    }

    /**
     * @return void
     */
    public function testValidateMinimumAmountVirtual(): void
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/include_discount_amount', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(true);
        $this->address->setAddressType(Address::TYPE_SHIPPING);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);

        $this->assertTrue($this->address->validateMinimumAmount());
    }

    /**
     * Provide data for test different cases
     *
     * @param void
     * @return array
     */
    public static function getDataProvider(): array
    {
        return [
            'Non-virtual Quote' => [
                'scopeConfigValues' => [
                    ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, 1, true],
                    ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, 1, 20],
                    ['sales/minimum_order/include_discount_amount', ScopeInterface::SCOPE_STORE, 1, true],
                    ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, 1, true]
                ],
                'address' => [
                    'setAddressType' => 'billing'
                ],
                'quote' => [
                    'getStoreId' => 1,
                    'getIsVirtual' => false
                ],
                'result' => true
            ],
            'With Shipping Discount' => [
                'scopeConfigValues' => [
                    ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, 1, true],
                    ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, 1, 2],
                    ['sales/minimum_order/include_discount_amount', ScopeInterface::SCOPE_STORE, 1, true],
                    ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, 1, true]
                ],
                'address' => [
                    'setBaseSubtotal' => 25.00,
                    'setBaseDiscountAmount' => -27.60,
                    'setBaseShippingDiscountAmount' => 4.6,
                    'setAddressType' => 'shipping'
                ],
                'quote' => [
                    'getStoreId' => 1,
                    'getIsVirtual' => false
                ],
                'result' => true
            ]
        ];
    }

    /**
     * Tests minimum order amount validation
     *
     * @param array $scopeConfigValues
     * @param array $address
     * @param array $quote
     * @param bool $result
     * @dataProvider getDataProvider
     *
     * @return void
     */
    public function testValidateMinimumAmount(
        array $scopeConfigValues,
        array $address,
        array $quote,
        bool $result
    ): void {
        foreach ($quote as $method => $value) {
            $this->quote->expects($this->once())
                ->method($method)
                ->willReturn($value);
        }

        foreach ($address as $setter => $value) {
            $this->address->$setter($value);
        }

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);

        $this->assertEquals($result, $this->address->validateMinimumAmount());
    }

    /**
     * @return void
     */
    public function testValidateMiniumumAmountWithoutDiscount(): void
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/include_discount_amount', ScopeInterface::SCOPE_STORE, $storeId, false],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);

        $this->assertTrue($this->address->validateMinimumAmount());
    }

    /**
     * @return void
     */
    public function testValidateMinimumAmountNegative(): void
    {
        $storeId = 1;
        $scopeConfigValues = [
            ['sales/minimum_order/active', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/amount', ScopeInterface::SCOPE_STORE, $storeId, 20],
            ['sales/minimum_order/include_discount_amount', ScopeInterface::SCOPE_STORE, $storeId, true],
            ['sales/minimum_order/tax_including', ScopeInterface::SCOPE_STORE, $storeId, true]
        ];

        $this->quote->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->quote->expects($this->once())
            ->method('getIsVirtual')
            ->willReturn(false);
        $this->address->setAddressType(Address::TYPE_SHIPPING);

        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->willReturnMap($scopeConfigValues);

        $this->assertTrue($this->address->validateMinimumAmount());
    }

    /**
     * @return void
     */
    public function testSetAndGetAppliedTaxes(): void
    {
        $data = ['data'];
        self::assertInstanceOf(Address::class, $this->address->setAppliedTaxes($data));
        self::assertEquals($data, $this->address->getAppliedTaxes());
    }

    /**
     * Checks a case, when applied taxes are not provided.
     *
     * @return void
     */
    public function testGetAppliedTaxesWithEmptyValue(): void
    {
        $this->address->setData('applied_taxes', null);
        self::assertEquals([], $this->address->getAppliedTaxes());
    }

    /**
     * Test of requesting shipping rates by address
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testRequestShippingRates(): void
    {
        $storeId = 12345;
        $webSiteId = 6789;
        $baseCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['convert'])
            ->addMethods(['getCurrentCurrencyCode'])
            ->getMockForAbstractClass();

        $currentCurrency = $this->getMockBuilder(Currency::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['convert'])
            ->addMethods(['getCurrentCurrencyCode'])
            ->getMockForAbstractClass();

        $currentCurrencyCode = 'UAH';

        $this->quote->expects($this->any())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->store->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn($webSiteId);

        $this->scopeConfig->expects($this->exactly(1))
            ->method('getValue')
            ->with(
                'tax/calculation/price_includes_tax',
                ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn(1);

        /** @var RateRequest */
        $request = $this->getMockBuilder(RateRequest::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setStoreId',
                    'setWebsiteId',
                    'setBaseCurrency',
                    'setPackageCurrency',
                    'getBaseSubtotalTotalInclTax',
                    'getBaseSubtotal'
                ]
            )
            ->getMock();

        /** @var Collection */
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collection->expects($this->once())
            ->method('setAddressFilter')
            ->willReturnSelf();

        /** @var RatesCollection */
        $ratesCollection = $this->getMockBuilder(RatesCollection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ratesCollection->expects($this->once())
            ->method('setAddressFilter')
            ->willReturnSelf();

        /** @var Result */
        $rates = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var  AbstractResult */
        $rateItem = $this->getMockBuilder(AbstractResult::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var Rate */
        $rate = $this->getMockBuilder(Rate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rate->expects($this->once())
            ->method('importShippingRate')
            ->willReturnSelf();

        $rates->expects($this->once())
            ->method('getAllRates')
            ->willReturn([$rateItem]);

        $this->requestFactory->expects($this->once())
            ->method('create')
            ->willReturn($request);

        $this->rateCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($ratesCollection);

        $this->rateCollector->expects($this->once())
            ->method('create')
            ->willReturn($this->rateCollection);

        $this->rateCollection->expects($this->once())
            ->method('collectRates')
            ->willReturnSelf();

        $this->rateCollection->expects($this->once())
            ->method('getResult')
            ->willReturn($rates);

        $this->itemCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->regionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->region);

        $this->region->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->storeManager->method('getStore')
            ->willReturnCallback(
                function ($arg) use ($storeId,) {
                    if ($arg === $storeId || is_null($arg)) {
                        return $this->store;
                    }
                }
            );

        $this->store->method('getBaseCurrency')
            ->willReturn($baseCurrency);

        $this->store->expects($this->once())
            ->method('getCurrentCurrency')
            ->willReturn($currentCurrency);

        $this->store->expects($this->once())
            ->method('getCurrentCurrencyCode')
            ->willReturn($currentCurrencyCode);

        $this->addressRateFactory->expects($this->once())
            ->method('create')
            ->willReturn($rate);

        $request->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);

        $request->expects($this->once())
            ->method('setWebsiteId')
            ->with($webSiteId);

        $request->expects($this->once())
            ->method('setBaseCurrency')
            ->with($baseCurrency);

        $request->expects($this->once())
            ->method('setPackageCurrency')
            ->with($currentCurrency);

        $baseCurrency->expects($this->once())
            ->method('convert')
            ->with(null, $currentCurrencyCode);

        $this->address->requestShippingRates();
    }
}
