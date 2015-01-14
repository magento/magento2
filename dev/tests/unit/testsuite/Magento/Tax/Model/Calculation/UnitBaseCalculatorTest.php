<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

class UnitBaseCalculatorTest extends \PHPUnit_Framework_TestCase
{
    const STORE_ID = 2300;
    const QUANTITY = 1;
    const UNIT_PRICE = 500;
    const RATE = 10;
    const STORE_RATE = 11;

    const CODE = 'CODE';
    const TYPE = 'TYPE';
    const ROW_TAX = 44.954135954136;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockTaxItemDetailsBuilder;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockCalculationTool;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockConfig;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mockAppliedTaxRateBuilder;

    /** @var UnitBaseCalculator */
    protected $model;

    protected $addressRateRequest;

    public function setUp()
    {
        $this->mockTaxItemDetailsBuilder = $this->getMockBuilder('Magento\Tax\Api\Data\TaxDetailsItemDataBuilder')
            ->setMethods([
                'setCode', 'setType', 'setTaxPercent', 'setPrice', 'setPriceInclTax', 'setRowTotal',
                'setRowTotalInclTax', 'setRowTax', 'create', 'populateWithArray', 'setTaxableAmount',
                'setDiscountAmount', 'setDiscountTaxCompensationAmount', 'setAppliedTaxes', 'setAssociatedItemCode',
            ])->disableOriginalConstructor()
            ->getMock();
        $this->mockCalculationTool = $this->getMockBuilder('\Magento\Tax\Model\Calculation')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'round', 'getRate', 'getStoreRate', 'getRateRequest', 'getAppliedRates'])
            ->getMock();
        $this->mockCalculationTool->expects($this->any())
            ->method('round')
            ->withAnyParameters()
            ->will($this->returnArgument(0));
        $this->mockConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressRateRequest = new \Magento\Framework\Object();

        $this->mockAppliedTaxRateBuilder = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxRateDataBuilder',
            ['setAmount', 'setTaxRateKey', 'setPercent', 'setRates', 'create', 'populateWithArray'],
            [],
            '',
            false
        );

        $appliedTaxBuilder = $this->getMock(
            'Magento\Tax\Api\Data\AppliedTaxDataBuilder',
            ['setAmount', 'setTaxRateKey', 'setPercent', 'setRates', 'create', 'populateWithArray'],
            [],
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = [
            'taxDetailsItemBuilder' => $this->mockTaxItemDetailsBuilder,
            'calculationTool'       => $this->mockCalculationTool,
            'config'                => $this->mockConfig,
            'storeId'               => self::STORE_ID,
            'addressRateRequest'    => $this->addressRateRequest,
            'appliedRateBuilder'    => $this->mockAppliedTaxRateBuilder,
            'appliedTaxBuilder'    => $appliedTaxBuilder,
        ];
        $this->model = $objectManager->getObject('Magento\Tax\Model\Calculation\UnitBaseCalculator', $arguments);
    }

    public function testCalculateWithTaxInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->once())
            ->method('getTaxIncluded')
            ->will($this->returnValue(true));

        $this->mockConfig->expects($this->once())
            ->method('crossBorderTradeEnabled')
            ->will($this->returnValue(false));
        $this->mockConfig->expects($this->once())
            ->method('applyTaxAfterDiscount')
            ->will($this->returnValue(true));

        $this->mockCalculationTool->expects($this->once())
            ->method('getRate')
            ->with($this->addressRateRequest)
            ->will($this->returnValue(self::RATE));
        $this->mockCalculationTool->expects($this->once())
            ->method('getStoreRate')
            ->with($this->addressRateRequest, self::STORE_ID)
            ->will($this->returnValue(self::STORE_RATE));
        $this->mockCalculationTool->expects($this->once())
            ->method('getAppliedRates')
            ->withAnyParameters()
            ->will($this->returnValue([]));

        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setCode')
            ->with(self::CODE);
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setType')
            ->with(self::TYPE);
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setRowTax')
            ->with(self::ROW_TAX);
        $expectedReturnValue = 'SOME RETURN OBJECT';
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedReturnValue));

        $this->assertSame($expectedReturnValue, $this->model->calculate($mockItem, self::QUANTITY));
    }

    public function testCalculateWithTaxNotInPrice()
    {
        $mockItem = $this->getMockItem();
        $mockItem->expects($this->once())
            ->method('getTaxIncluded')
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

        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setCode')
            ->with(self::CODE);
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setType')
            ->with(self::TYPE);
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('setRowTax')
            ->with(0.0);
        $expectedReturnValue = 'SOME RETURN OBJECT';
        $this->mockTaxItemDetailsBuilder->expects($this->once())
            ->method('create')
            ->will($this->returnValue($expectedReturnValue));

        $this->assertSame($expectedReturnValue, $this->model->calculate($mockItem, self::QUANTITY));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockItem()
    {
        /** @var $mockItem \PHPUnit_Framework_MockObject_MockObject */
        $mockItem = $this->getMockBuilder('Magento\Tax\Api\Data\QuoteDetailsItemInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $mockItem->expects($this->once())
            ->method('getDiscountAmount')
            ->will($this->returnValue(1));
        $mockItem->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue(self::CODE));
        $mockItem->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(self::TYPE));
        $mockItem->expects($this->once())
            ->method('getUnitPrice')
            ->will($this->returnValue(self::UNIT_PRICE));

        return $mockItem;
    }
}
