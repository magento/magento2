<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Quote\ItemDetails;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Tax\Model\TaxClass\Key as TaxClassKey;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommonTaxCollectorTest extends TestCase
{
    /**
     * @var CommonTaxCollector
     */
    private CommonTaxCollector $commonTaxCollector;

    /**
     * @var MockObject|Config
     */
    private Config $taxConfig;

    /**
     * @var MockObject|QuoteAddress
     */
    private QuoteAddress $address;

    /**
     * @var MockObject|Quote
     */
    private Quote $quote;

    /**
     * @var MockObject|Store
     */
    private Store $store;

    /**
     * @var TaxClassKeyInterfaceFactory|MockObject
     */
    private TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactoryMock;

    /**
     * @var QuoteDetailsItemInterfaceFactory|MockObject
     */
    private QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactoryMock;

    /**
     * @var QuoteDetailsItemInterface|MockObject
     */
    private QuoteDetailsItemInterface $quoteDetailsItemDataObject;

    /**
     * @var TaxClassKeyInterface|MockObject
     */
    private TaxClassKeyInterface $taxClassKeyDataObject;

    /**
     * @var TaxHelper|MockObject
     */
    private TaxHelper $taxHelper;

    /**
     * @var TaxCalculationInterface|TaxCalculationInterface&MockObject|MockObject
     */
    private TaxCalculationInterface $taxCalculation;

    /**
     * @var QuoteDetailsInterfaceFactory|QuoteDetailsInterfaceFactory&MockObject|MockObject
     */
    private QuoteDetailsInterfaceFactory $quoteDetailsFactory;

    /**
     * @var AddressInterfaceFactory|AddressInterfaceFactory&MockObject|MockObject
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @var RegionInterfaceFactory|RegionInterfaceFactory&MockObject|MockObject
     */
    private RegionInterfaceFactory $regionFactory;

    /**
     * @var QuoteDetailsItemExtensionInterfaceFactory|QuoteDetailsItemExtensionInterfaceFactory&MockObject|MockObject
     */
    private QuoteDetailsItemExtensionInterfaceFactory $quoteDetailsItemExtensionFactory;

    /**
     * @var AccountManagementInterface|AccountManagementInterface&MockObject|MockObject
     */
    private AccountManagementInterface $accountManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->taxConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__wakeup'])
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__wakeup', 'getStore'])
            ->getMock();

        $this->quote
            ->method('getStore')
            ->willReturn($this->store);

        $this->address = $this->getMockBuilder(QuoteAddress::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->address
            ->method('getQuote')
            ->willReturn($this->quote);
        $methods = ['create'];
        $this->quoteDetailsItemDataObject = $this->createMock(ItemDetails::class);
        $this->quoteDetailsItemDataObject->method('setType')->willReturnSelf();
        $this->quoteDetailsItemDataObject->method('setCode')->willReturnSelf();
        $this->quoteDetailsItemDataObject->method('setQuantity')->willReturnSelf();
        $this->taxClassKeyDataObject = $this->createMock(TaxClassKey::class);
        $this->taxClassKeyDataObject->method('setType')->willReturnSelf();
        $this->taxClassKeyDataObject->method('setValue')->willReturnSelf();
        $this->quoteDetailsItemDataObjectFactoryMock
            = $this->createPartialMock(QuoteDetailsItemInterfaceFactory::class, $methods);
        $this->quoteDetailsItemDataObjectFactoryMock
            ->method('create')
            ->willReturn($this->quoteDetailsItemDataObject);
        $this->taxClassKeyDataObjectFactoryMock =
            $this->createPartialMock(TaxClassKeyInterfaceFactory::class, $methods);
        $this->taxClassKeyDataObjectFactoryMock
            ->method('create')
            ->willReturn($this->taxClassKeyDataObject);
        $this->taxHelper = $this->getMockBuilder(TaxHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxCalculation = $this->createMock(TaxCalculationInterface::class);
        $this->quoteDetailsFactory = $this->createMock(QuoteDetailsInterfaceFactory::class);
        $this->addressFactory = $this->createMock(AddressInterfaceFactory::class);
        $this->regionFactory = $this->createMock(RegionInterfaceFactory::class);
        $this->quoteDetailsItemExtensionFactory = $this->createMock(QuoteDetailsItemExtensionInterfaceFactory::class);
        $this->accountManagement = $this->createMock(AccountManagementInterface::class);
        $this->commonTaxCollector = new CommonTaxCollector(
            $this->taxConfig,
            $this->taxCalculation,
            $this->quoteDetailsFactory,
            $this->quoteDetailsItemDataObjectFactoryMock,
            $this->taxClassKeyDataObjectFactoryMock,
            $this->addressFactory,
            $this->regionFactory,
            $this->taxHelper,
            $this->quoteDetailsItemExtensionFactory,
            $this->accountManagement
        );

        parent::setUp();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testMapAddress(): void
    {
        $countryId = 1;
        $regionId = 2;
        $regionCode = 'regionCode';
        $region = 'region';
        $postCode = 'postCode';
        $city = 'city';
        $street = ['street'];

        $address = $this->createMock(QuoteAddress::class);
        $address->expects($this->once())->method('getCountryId')->willReturn($countryId);
        $address->expects($this->once())->method('getRegionId')->willReturn($regionId);
        $address->expects($this->once())->method('getRegionCode')->willReturn($regionCode);
        $address->expects($this->once())->method('getRegion')->willReturn($region);
        $address->expects($this->once())->method('getPostcode')->willReturn($postCode);
        $address->expects($this->once())->method('getCity')->willReturn($city);
        $address->expects($this->once())->method('getStreet')->willReturn($street);

        $regionData = [
            'data' => [
                'region_id' => $regionId,
                'region_code' => $regionCode,
                'region' => $region,
            ]
        ];
        $regionObject = $this->createMock(RegionInterface::class);
        $this->regionFactory->expects($this->once())->method('create')->with($regionData)->willReturn($regionObject);
        $customerAddress = $this->createMock(AddressInterface::class);

        $this->addressFactory->expects($this->once())
            ->method('create')
            ->with(
                [
                    'data' => [
                        'country_id' => $countryId,
                        'region' => $regionObject,
                        'postcode' => $postCode,
                        'city' => $city,
                        'street' => $street
                    ]
                ]
            )
            ->willReturn($customerAddress);

        $this->assertSame($customerAddress, $this->commonTaxCollector->mapAddress($address));
    }

    /**
     * Test for GetShippingDataObject
     *
     * @param array $addressData
     * @param bool $useBaseCurrency
     * @param string $shippingTaxClass
     * @param bool $shippingPriceInclTax
     *
     * @return void
     * @dataProvider getShippingDataObjectDataProvider
     * @throws Exception
     */
    public function testGetShippingDataObject(
        array $addressData,
        bool $useBaseCurrency,
        string $shippingTaxClass,
        bool $shippingPriceInclTax
    ): void {
        $shippingAssignmentMock = $this->getMockForAbstractClass(ShippingAssignmentInterface::class);
        /** @var MockObject|QuoteAddressTotal $totalsMock */
        $totalsMock = $this->getMockBuilder(QuoteAddressTotal::class)
            ->addMethods(
                [
                    'getShippingDiscountAmount',
                    'getShippingTaxCalculationAmount',
                    'setShippingTaxCalculationAmount',
                    'getShippingAmount',
                    'setBaseShippingTaxCalculationAmount',
                    'getBaseShippingAmount',
                    'getBaseShippingDiscountAmount'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $shippingMock = $this->getMockForAbstractClass(ShippingInterface::class);
        /** @var MockObject|ShippingAssignmentInterface $shippingAssignmentMock */
        $shippingAssignmentMock->expects($this->once())->method('getShipping')->willReturn($shippingMock);
        $shippingMock->expects($this->once())->method('getAddress')->willReturn($this->address);
        $baseShippingAmount = $addressData['base_shipping_amount'];
        $shippingAmount = $addressData['shipping_amount'];
        $totalsMock->method('getShippingTaxCalculationAmount')->willReturn($shippingAmount);
        $this->taxConfig
            ->method('getShippingTaxClass')
            ->with($this->store)
            ->willReturn($shippingTaxClass);
        $this->taxConfig
            ->method('shippingPriceIncludesTax')
            ->with($this->store)
            ->willReturn($shippingPriceInclTax);
        $totalsMock
            ->expects($this->atLeastOnce())
            ->method('getShippingDiscountAmount')
            ->willReturn($shippingAmount);
        if ($shippingAmount) {
            if ($useBaseCurrency && $shippingAmount != 0) {
                $totalsMock
                    ->expects($this->once())
                    ->method('getBaseShippingDiscountAmount')
                    ->willReturn($baseShippingAmount);
            } else {
                $totalsMock->expects($this->never())->method('getBaseShippingDiscountAmount');
            }
        }
        foreach ($addressData as $key => $value) {
            $totalsMock->setData($key, $value);
        }
        $this->assertEquals(
            $this->quoteDetailsItemDataObject,
            $this->commonTaxCollector->getShippingDataObject($shippingAssignmentMock, $totalsMock, $useBaseCurrency)
        );
    }

    /**
     * Update item tax info
     *
     * @return void
     */
    public function testUpdateItemTaxInfo(): void
    {
        /** @var MockObject|QuoteItem $quoteItem */
        $quoteItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomPrice'])
            ->onlyMethods(['getPrice', 'setPrice', 'setCustomPrice'])
            ->getMock();
        $this->taxHelper->method('applyTaxOnCustomPrice')->willReturn(true);
        $quoteItem->method('getCustomPrice')->willReturn(true);
        /** @var MockObject|TaxDetailsItemInterface $itemTaxDetails */
        $itemTaxDetails = $this->getMockBuilder(TaxDetailsItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        /** @var MockObject|TaxDetailsItemInterface $baseItemTaxDetails */
        $baseItemTaxDetails = $this->getMockBuilder(TaxDetailsItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $quoteItem->expects($this->once())->method('setCustomPrice');

        $this->commonTaxCollector->updateItemTaxInfo(
            $quoteItem,
            $itemTaxDetails,
            $baseItemTaxDetails,
            $this->store
        );
    }

    /**
     * Data for testGetShippingDataObject
     *
     * @return array
     */
    public static function getShippingDataObjectDataProvider(): array
    {
        $data = [
            'free_shipping' => [
                'addressData' => [
                    'shipping_amount' => 0,
                    'base_shipping_amount' => 0,
                ],
                'useBaseCurrency' => false,
                'shippingTaxClass' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
            'none_zero_none_base' => [
                'addressData' => [
                    'shipping_amount' => 10,
                    'base_shipping_amount' => 5,
                ],
                'useBaseCurrency' => false,
                'shippingTaxClass' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
            'none_zero_base' => [
                'addressData' => [
                    'shipping_amount' => 10,
                    'base_shipping_amount' => 5,
                ],
                'useBaseCurrency' => true,
                'shippingTaxClass' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
        ];

        return $data;
    }
}
