<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Sales\Total\Quote;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Api\Data\ShippingInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\Sales\Quote\ItemDetails;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Magento\Tax\Model\TaxClass\Key as TaxClassKey;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommonTaxCollectorTest extends TestCase
{
    /**
     * @var CommonTaxCollector
     */
    private $commonTaxCollector;

    /**
     * @var MockObject|Config
     */
    private $taxConfig;

    /**
     * @var MockObject|QuoteAddress
     */
    private $address;

    /**
     * @var MockObject|Quote
     */
    private $quote;

    /**
     * @var MockObject|Store
     */
    private $store;

    /**
     * @var MockObject
     */
    protected $taxClassKeyDataObjectFactoryMock;

    /**
     * @var MockObject
     */
    protected $quoteDetailsItemDataObjectFactoryMock;

    /**
     * @var QuoteDetailsItemInterface
     */
    protected $quoteDetailsItemDataObject;

    /**
     * @var TaxClassKeyInterface
     */
    protected $taxClassKeyDataObject;

    /**
     * @var TaxHelper
     */
    private $taxHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->taxConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getShippingTaxClass', 'shippingPriceIncludesTax', 'discountTax'])
            ->getMock();

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getStore'])
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
        $this->quoteDetailsItemDataObject = $objectManager->getObject(ItemDetails::class);
        $this->taxClassKeyDataObject = $objectManager->getObject(TaxClassKey::class);
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
        $this->commonTaxCollector = $objectManager->getObject(
            CommonTaxCollector::class,
            [
                'taxConfig' => $this->taxConfig,
                'quoteDetailsItemDataObjectFactory' => $this->quoteDetailsItemDataObjectFactoryMock,
                'taxClassKeyDataObjectFactory' => $this->taxClassKeyDataObjectFactoryMock,
                'taxHelper' => $this->taxHelper,
            ]
        );
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
     */
    public function testGetShippingDataObject(
        array $addressData,
        $useBaseCurrency,
        $shippingTaxClass,
        $shippingPriceInclTax
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
                $expectedDiscountAmount = $baseShippingAmount;
            } else {
                $totalsMock->expects($this->never())->method('getBaseShippingDiscountAmount');
                $expectedDiscountAmount = $shippingAmount;
            }
        }
        foreach ($addressData as $key => $value) {
            $totalsMock->setData($key, $value);
        }
        $this->assertEquals(
            $this->quoteDetailsItemDataObject,
            $this->commonTaxCollector->getShippingDataObject($shippingAssignmentMock, $totalsMock, $useBaseCurrency)
        );

        if ($shippingAmount) {
            $this->assertEquals($expectedDiscountAmount, $this->quoteDetailsItemDataObject->getDiscountAmount());
        }
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
            ->setMethods(['getPrice', 'setPrice', 'getCustomPrice', 'setCustomPrice'])
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
    public function getShippingDataObjectDataProvider(): array
    {
        $data = [
            'free_shipping' => [
                'address' => [
                    'shipping_amount' => 0,
                    'base_shipping_amount' => 0,
                ],
                'use_base_currency' => false,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
            'none_zero_none_base' => [
                'address' => [
                    'shipping_amount' => 10,
                    'base_shipping_amount' => 5,
                ],
                'use_base_currency' => false,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
            'none_zero_base' => [
                'address' => [
                    'shipping_amount' => 10,
                    'base_shipping_amount' => 5,
                ],
                'use_base_currency' => true,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
            ],
        ];

        return $data;
    }
}
