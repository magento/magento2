<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\Calculation;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Tax\Api\Data\AppliedTaxInterfaceFactory;
use Magento\Tax\Api\Data\AppliedTaxRateInterface;
use Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\UnitBaseCalculator;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxDetails\AppliedTax;
use Magento\Tax\Model\TaxDetails\AppliedTaxRate;
use Magento\Tax\Model\TaxDetails\ItemDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnitBaseCalculatorTest extends TestCase
{
    public const STORE_ID = 2300;
    public const QUANTITY = 1;
    public const UNIT_PRICE = 500;
    public const RATE = 10;
    public const STORE_RATE = 11;

    public const CODE = 'CODE';
    public const TYPE = 'TYPE';
    public const ROW_TAX = 44.958682408681;
    public const ROW_TAX_ROUNDED = 44.95;
    public const PRICE_INCL_TAX = 495.4954954955;
    public const PRICE_INCL_TAX_ROUNDED = 495.50;

    /** @var MockObject */
    protected $taxDetailsItemDataObjectFactoryMock;

    /** @var MockObject */
    protected $mockCalculationTool;

    /** @var MockObject */
    protected $mockConfig;

    /** @var MockObject */
    protected $appliedTaxRateDataObjectFactoryMock;

    /** @var UnitBaseCalculator */
    protected $model;

    /** @var DataObject */
    protected $addressRateRequest;

    /**
     * @var TaxDetailsItemInterface
     */
    protected $taxDetailsItem;

    /**
     * @var AppliedTaxRateInterface
     */
    protected $appliedTaxRate;

    protected function setUp(): void
    {
        /** @var ObjectManager  $objectManager */
        $objectManager = new ObjectManager($this);
        $this->taxDetailsItem = $objectManager->getObject(ItemDetails::class);
        $this->taxDetailsItemDataObjectFactoryMock =
            $this->getMockBuilder(TaxDetailsItemInterfaceFactory::class)
                ->setMethods(['create'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->taxDetailsItemDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->taxDetailsItem);

        $this->mockCalculationTool = $this->getMockBuilder(Calculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates'])
            ->getMock();
        $this->mockCalculationTool->expects($this->any())
            ->method('round')
            ->withAnyParameters()
            ->willReturnCallback(
                function ($price) {
                    return round((float) $price, 2);
                }
            );
        $this->mockConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRateRequest = new DataObject();

        $this->appliedTaxRate = $objectManager->getObject(AppliedTaxRate::class);
        $this->appliedTaxRateDataObjectFactoryMock = $this->createPartialMock(
            AppliedTaxRateInterfaceFactory::class,
            ['create']
        );
        $this->appliedTaxRateDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxRate);

        $appliedTaxDataObject = $objectManager->getObject(AppliedTax::class);
        $appliedTaxDataObjectFactoryMock = $this->createPartialMock(
            AppliedTaxInterfaceFactory::class,
            ['create']
        );
        $appliedTaxDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($appliedTaxDataObject);

        $arguments = [
            'taxDetailsItemDataObjectFactory' => $this->taxDetailsItemDataObjectFactoryMock,
            'calculationTool'       => $this->mockCalculationTool,
            'config'                => $this->mockConfig,
            'storeId'               => self::STORE_ID,
            'addressRateRequest'    => $this->addressRateRequest,
            'appliedRateDataObjectFactory'    => $this->appliedTaxRateDataObjectFactoryMock,
            'appliedTaxDataObjectFactory'    => $appliedTaxDataObjectFactoryMock,
        ];
        $this->model = $objectManager->getObject(UnitBaseCalculator::class, $arguments);
    }

    public function testCalculateWithTaxInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->atLeastOnce())
            ->method('getIsTaxIncluded')
            ->willReturn(true);

        $this->mockConfig->expects($this->atLeastOnce())
            ->method('crossBorderTradeEnabled')
            ->willReturn(false);
        $this->mockConfig->expects($this->atLeastOnce())
            ->method('applyTaxAfterDiscount')
            ->willReturn(true);

        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getRate')
            ->with($this->addressRateRequest)
            ->willReturn(self::RATE);
        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getStoreRate')
            ->with($this->addressRateRequest, self::STORE_ID)
            ->willReturn(self::STORE_RATE);
        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getAppliedRates')
            ->withAnyParameters()
            ->willReturn([]);

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY));
        $this->assertSame(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertSame(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertEquals(self::ROW_TAX_ROUNDED, $this->taxDetailsItem->getRowTax());
        $this->assertEquals(self::PRICE_INCL_TAX_ROUNDED, $this->taxDetailsItem->getPriceInclTax());

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY, false));
        $this->assertSame(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertSame(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertEquals(self::ROW_TAX, $this->taxDetailsItem->getRowTax());
        $this->assertEquals(self::PRICE_INCL_TAX, $this->taxDetailsItem->getPriceInclTax());
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->once())
            ->method('getIsTaxIncluded')
            ->willReturn(false);

        $this->mockConfig->expects($this->once())
            ->method('applyTaxAfterDiscount')
            ->willReturn(true);

        $this->mockCalculationTool->expects($this->once())
            ->method('getRate')
            ->with($this->addressRateRequest)
            ->willReturn(self::RATE);
        $this->mockCalculationTool->expects($this->once())
            ->method('getAppliedRates')
            ->withAnyParameters()
            ->willReturn([['id' => 0, 'percent' => 0, 'rates' => []]]);

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY));
        $this->assertEquals(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertEquals(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertEquals(0.0, $this->taxDetailsItem->getRowTax());
    }

    /**
     * @return MockObject
     */
    protected function getMockItem()
    {
        /** @var MockObject $mockItem */
        $mockItem = $this->getMockBuilder(QuoteDetailsItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockItem->expects($this->atLeastOnce())
            ->method('getDiscountAmount')
            ->willReturn(1);
        $mockItem->expects($this->atLeastOnce())
            ->method('getCode')
            ->willReturn(self::CODE);
        $mockItem->expects($this->atLeastOnce())
            ->method('getType')
            ->willReturn(self::TYPE);
        $mockItem->expects($this->atLeastOnce())
            ->method('getUnitPrice')
            ->willReturn(self::UNIT_PRICE);

        return $mockItem;
    }
}
