<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Model\Calculation;

use \Magento\Tax\Model\Calculation\UnitBaseCalculator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UnitBaseCalculatorTest extends \PHPUnit_Framework_TestCase
{
    const STORE_ID = 2300;
    const QUANTITY = 1;
    const UNIT_PRICE = 500;
    const RATE = 10;
    const STORE_RATE = 11;

    const CODE = 'CODE';
    const TYPE = 'TYPE';
    const ROW_TAX = 44.958682408681;
    const ROW_TAX_ROUNDED = 44.95;
    const PRICE_INCL_TAX = 495.4954954955;
    const PRICE_INCL_TAX_ROUNDED = 495.50;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $taxDetailsItemDataObjectFactoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockCalculationTool;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $appliedTaxRateDataObjectFactoryMock;

    /** @var UnitBaseCalculator */
    protected $model;

    protected $addressRateRequest;

    /**
     * @var \Magento\Tax\Api\Data\TaxDetailsItemInterface
     */
    protected $taxDetailsItem;

    /**
     * @var \Magento\Tax\Api\Data\AppliedTaxRateInterface
     */
    protected $appliedTaxRate;

    protected function setUp()
    {
        /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager  $objectManager */
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->taxDetailsItem = $objectManager->getObject(\Magento\Tax\Model\TaxDetails\ItemDetails::class);
        $this->taxDetailsItemDataObjectFactoryMock =
            $this->getMockBuilder(\Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxDetailsItemDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->taxDetailsItem);

        $this->mockCalculationTool = $this->getMockBuilder(\Magento\Tax\Model\Calculation::class)
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates'])
            ->getMock();
        $this->mockCalculationTool->expects($this->any())
            ->method('round')
            ->withAnyParameters()
            ->willReturnCallback(
                function ($price) {
                    return round($price, 2);
                }
            );
        $this->mockConfig = $this->getMockBuilder(\Magento\Tax\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRateRequest = new \Magento\Framework\DataObject();

        $this->appliedTaxRate = $objectManager->getObject(\Magento\Tax\Model\TaxDetails\AppliedTaxRate::class);
        $this->appliedTaxRateDataObjectFactoryMock = $this->getMock(
            \Magento\Tax\Api\Data\AppliedTaxRateInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->appliedTaxRateDataObjectFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->appliedTaxRate);

        $appliedTaxDataObject = $objectManager->getObject(\Magento\Tax\Model\TaxDetails\AppliedTax::class);
        $appliedTaxDataObjectFactoryMock = $this->getMock(
            \Magento\Tax\Api\Data\AppliedTaxInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
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
        $this->model = $objectManager->getObject(\Magento\Tax\Model\Calculation\UnitBaseCalculator::class, $arguments);
    }

    public function testCalculateWithTaxInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->atLeastOnce())
            ->method('getIsTaxIncluded')
            ->will($this->returnValue(true));

        $this->mockConfig->expects($this->atLeastOnce())
            ->method('crossBorderTradeEnabled')
            ->will($this->returnValue(false));
        $this->mockConfig->expects($this->atLeastOnce())
            ->method('applyTaxAfterDiscount')
            ->will($this->returnValue(true));

        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getRate')
            ->with($this->addressRateRequest)
            ->will($this->returnValue(self::RATE));
        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getStoreRate')
            ->with($this->addressRateRequest, self::STORE_ID)
            ->will($this->returnValue(self::STORE_RATE));
        $this->mockCalculationTool->expects($this->atLeastOnce())
            ->method('getAppliedRates')
            ->withAnyParameters()
            ->will($this->returnValue([]));

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY));
        $this->assertSame(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertSame(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertSame(self::ROW_TAX_ROUNDED, $this->taxDetailsItem->getRowTax());
        $this->assertEquals(self::PRICE_INCL_TAX_ROUNDED, $this->taxDetailsItem->getPriceInclTax());

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY, false));
        $this->assertSame(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertSame(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertSame(self::ROW_TAX, $this->taxDetailsItem->getRowTax());
        $this->assertEquals(self::PRICE_INCL_TAX, $this->taxDetailsItem->getPriceInclTax());
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->once())
            ->method('getIsTaxIncluded')
            ->will($this->returnValue(false));

        $this->mockConfig->expects($this->once())
            ->method('applyTaxAfterDiscount')
            ->will($this->returnValue(true));

        $this->mockCalculationTool->expects($this->once())
            ->method('getRate')
            ->with($this->addressRateRequest)
            ->will($this->returnValue(self::RATE));
        $this->mockCalculationTool->expects($this->once())
            ->method('getAppliedRates')
            ->withAnyParameters()
            ->will($this->returnValue([['id' => 0, 'percent' => 0, 'rates' => []]]));

        $this->assertSame($this->taxDetailsItem, $this->model->calculate($mockItem, self::QUANTITY));
        $this->assertEquals(self::CODE, $this->taxDetailsItem->getCode());
        $this->assertEquals(self::TYPE, $this->taxDetailsItem->getType());
        $this->assertEquals(0.0, $this->taxDetailsItem->getRowTax());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockItem()
    {
        /** @var $mockItem \PHPUnit_Framework_MockObject_MockObject */
        $mockItem = $this->getMockBuilder(\Magento\Tax\Api\Data\QuoteDetailsItemInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockItem->expects($this->atLeastOnce())
            ->method('getDiscountAmount')
            ->will($this->returnValue(1));
        $mockItem->expects($this->atLeastOnce())
            ->method('getCode')
            ->will($this->returnValue(self::CODE));
        $mockItem->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue(self::TYPE));
        $mockItem->expects($this->atLeastOnce())
            ->method('getUnitPrice')
            ->will($this->returnValue(self::UNIT_PRICE));

        return $mockItem;
    }
}
