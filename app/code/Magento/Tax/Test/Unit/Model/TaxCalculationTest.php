<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TaxCalculationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxDetailsItemDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxDetailsDataObjectFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $calculatorFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $calculationTool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxClassManagementMock;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataObjectHelperMock;

    protected function setUp()
    {
        $this->calculationTool = $this->getMock('\Magento\Tax\Model\Calculation', [], [], '', false);
        $this->calculatorFactory = $this->getMock(
            '\Magento\Tax\Model\Calculation\CalculatorFactory',
            [],
            [],
            '',
            false
        );
        $this->configMock = $this->getMock('\Magento\Tax\Model\Config', [], [], '', false);
        $this->taxDetailsDataObjectFactory = $this->getMock(
            '\Magento\Tax\Api\Data\TaxDetailsInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->taxDetailsItemDataObjectFactory = $this->getMock(
            '\Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory',
            [],
            [],
            '',
            false
        );
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->dataObjectHelperMock = $this->getMockBuilder('\Magento\Framework\Api\DataObjectHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxClassManagementMock = $this->getMock('\Magento\Tax\Api\TaxClassManagementInterface');

        $objectManager = new ObjectManager($this);
        $this->taxCalculationService = $objectManager->getObject(
            'Magento\Tax\Model\TaxCalculation',
            [
                'calculation' => $this->calculationTool,
                'calculatorFactory' => $this->calculatorFactory,
                'config' => $this->configMock,
                'taxDetailsDataObjectFactory' => $this->taxDetailsDataObjectFactory,
                'taxDetailsItemDataObjectFactory' => $this->taxDetailsItemDataObjectFactory,
                'storeManager' => $this->storeManager,
                'taxClassManagement' => $this->taxClassManagementMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
            ]
        );
    }

    public function testGetCalculatedRate()
    {
        $productTaxClassId = 1;
        $customerId = 2;
        $storeId = 3;
        $rate = 0.5;

        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getStoreId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->getMock('\Magento\Framework\DataObject', ['setProductClassId'], [], '', false);
        $this->calculationTool->expects($this->once())
            ->method('getRateRequest')
            ->with(null, null, null, $storeId, $customerId)
            ->willReturn($rateRequestMock);

        $rateRequestMock->expects($this->once())
            ->method('setProductClassId')
            ->with($productTaxClassId)
            ->willReturnSelf();

        $this->calculationTool->expects($this->once())->method('getRate')->with($rateRequestMock)->willReturn($rate);
        $this->assertEquals(
            $rate,
            $this->taxCalculationService->getCalculatedRate($productTaxClassId, $customerId, null)
        );
    }

    public function testGetDefaultCalculatedRate()
    {
        $productTaxClassId = 1;
        $customerId = 2;
        $storeId = 3;
        $rate = 0.5;

        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getStoreId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->getMock('\Magento\Framework\DataObject', ['setProductClassId'], [], '', false);
        $this->calculationTool->expects($this->once())
            ->method('getDefaultRateRequest')
            ->with($storeId, $customerId)
            ->willReturn($rateRequestMock);

        $rateRequestMock->expects($this->once())
            ->method('setProductClassId')
            ->with($productTaxClassId)
            ->willReturnSelf();

        $this->calculationTool->expects($this->once())->method('getRate')->with($rateRequestMock)->willReturn($rate);
        $this->assertEquals(
            $rate,
            $this->taxCalculationService->getDefaultCalculatedRate($productTaxClassId, $customerId, null)
        );
    }

    public function testCalculateTaxIfNoItemsInQuote()
    {
        $storeId = 3;
        $quoteDetailsMock = $this->getMock('\Magento\Tax\Api\Data\QuoteDetailsInterface');

        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getStoreId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $quoteDetailsMock->expects($this->once())->method('getItems')->willReturn(null);

        $taxDetailsMock = $this->getMock('\Magento\Tax\Api\Data\TaxDetailsInterface');
        $taxDetailsMock->expects($this->once())
            ->method('setSubtotal')
            ->willReturnSelf();
        $taxDetailsMock->expects($this->once())
            ->method('setTaxAmount')
            ->willReturnSelf();
        $taxDetailsMock->expects($this->once())
            ->method('setDiscountTaxCompensationAmount')
            ->willReturnSelf();
        $taxDetailsMock->expects($this->once())
            ->method('setAppliedTaxes')
            ->willReturnSelf();
        $taxDetailsMock->expects($this->once())
            ->method('setItems')
            ->willReturnSelf();
        $this->taxDetailsDataObjectFactory->expects($this->once())->method('create')->willReturn($taxDetailsMock);

        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }

    public function testCalculateTax()
    {
        $storeId = 3;
        $algorithm = 'algorithm';
        $customerId = 100;
        $taxClassId = 200;
        $taxDetailsData = [
            \Magento\Tax\Model\TaxDetails\TaxDetails::KEY_SUBTOTAL => 0.0,
            \Magento\Tax\Model\TaxDetails\TaxDetails::KEY_TAX_AMOUNT => 0.0,
            \Magento\Tax\Model\TaxDetails\TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            \Magento\Tax\Model\TaxDetails\TaxDetails::KEY_APPLIED_TAXES => [],
            \Magento\Tax\Model\TaxDetails\TaxDetails::KEY_ITEMS => [],
        ];

        $quoteDetailsMock = $this->getMock('\Magento\Tax\Api\Data\QuoteDetailsInterface');

        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getStoreId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $billAddressMock = $this->getMock('Magento\Customer\Api\Data\AddressInterface', [], [], '', false);
        $shipAddressMock = $this->getMock('Magento\Customer\Api\Data\AddressInterface', [], [], '', false);
        $taxClassKeyMock = $this->getMock('\Magento\Tax\Api\Data\TaxClassKeyInterface');

        $quoteDetailsItemMock = $this->getMock('\Magento\Tax\Api\Data\QuoteDetailsItemInterface');
        $quoteDetailsMock->expects($this->once())->method('getItems')->willReturn([$quoteDetailsItemMock]);
        $quoteDetailsMock->expects($this->once())->method('getBillingAddress')->willReturn($billAddressMock);
        $quoteDetailsMock->expects($this->once())->method('getShippingAddress')->willReturn($shipAddressMock);
        $quoteDetailsMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $quoteDetailsMock->expects($this->once())->method('getCustomerTaxClassKey')->willReturn($taxClassKeyMock);

        $this->configMock->expects($this->once())->method('getAlgorithm')->with($storeId)->willReturn($algorithm);
        $this->taxClassManagementMock->expects($this->once())
            ->method('getTaxClassId')
            ->with($taxClassKeyMock, 'customer')
            ->willReturn($taxClassId);

        $calculatorMock = $this->getMock('Magento\Tax\Model\Calculation\TotalBaseCalculator', [], [], '', false);
        $this->calculatorFactory->expects($this->once())
            ->method('create')
            ->with($algorithm, $storeId, $billAddressMock, $shipAddressMock, $taxClassId, $customerId)
            ->willReturn($calculatorMock);

        $taxDetailsMock = $this->getMock('\Magento\Tax\Api\Data\TaxDetailsItemInterface');
        $calculatorMock->expects($this->once())->method('calculate')->willReturn($taxDetailsMock);

        $taxDetailsMock = $this->getMock('\Magento\Tax\Api\Data\TaxDetailsInterface');
        $this->taxDetailsDataObjectFactory->expects($this->once())->method('create')->willReturn($taxDetailsMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($taxDetailsMock, $taxDetailsData)
            ->willReturnSelf();

        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }
}
