<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tax\Model;

use Magento\TestFramework\Helper\ObjectManager;

class TaxCalculationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxDetailsItemBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $taxDetailsBuilder;

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

    public function setUp()
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
        $this->taxDetailsBuilder = $this->getMock(
            '\Magento\Tax\Api\Data\TaxDetailsDataBuilder',
            ['populateWithArray', 'create', 'setItems'],
            [],
            '',
            false
        );
        $this->taxDetailsItemBuilder = $this->getMock(
            '\Magento\Tax\Api\Data\TaxDetailsItemDataBuilder',
            [],
            [],
            '',
            false
        );
        $this->storeManager = $this->getMock('\Magento\Store\Model\StoreManagerInterface');
        $this->taxClassManagementMock = $this->getMock('\Magento\Tax\Api\TaxClassManagementInterface');

        $objectManager = new ObjectManager($this);
        $this->taxCalculationService = $objectManager->getObject(
            'Magento\Tax\Model\TaxCalculation',
            [
                'calculation' => $this->calculationTool,
                'calculatorFactory' => $this->calculatorFactory,
                'config' => $this->configMock,
                'taxDetailsBuilder' => $this->taxDetailsBuilder,
                'taxDetailsItemBuilder' => $this->taxDetailsItemBuilder,
                'storeManager' => $this->storeManager,
                'taxClassManagement' => $this->taxClassManagementMock
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

        $rateRequestMock = $this->getMock('\Magento\Framework\Object', ['setProductClassId'], [], '', false);
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

        $rateRequestMock = $this->getMock('\Magento\Framework\Object', ['setProductClassId'], [], '', false);
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
        $taxDetailsData = [
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_SUBTOTAL => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_TAX_AMOUNT => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_APPLIED_TAXES => [],
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_ITEMS => [],
        ];
        $storeId = 3;
        $quoteDetailsMock = $this->getMock('\Magento\Tax\Api\Data\QuoteDetailsInterface');

        $storeMock = $this->getMock('\Magento\Store\Model\Store', ['getStoreId'], [], '', false);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $quoteDetailsMock->expects($this->once())->method('getItems')->willReturn(null);

        $this->taxDetailsBuilder->expects($this->once())
            ->method('populateWithArray')
            ->with($taxDetailsData)
            ->willReturnSelf();

        $taxDetailsMock = $this->getMock('\Magento\Tax\Api\Data\TaxDetailsInterface');
        $this->taxDetailsBuilder->expects($this->once())->method('create')->willReturn($taxDetailsMock);
        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }

    public function testCalculateTax()
    {
        $storeId = 3;
        $algorithm = 'algorithm';
        $customerId = 100;
        $taxClassId = 200;
        $taxDetailsData = [
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_SUBTOTAL => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_TAX_AMOUNT => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_APPLIED_TAXES => [],
            \Magento\Tax\Api\Data\TaxDetailsInterface::KEY_ITEMS => [],
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

        $this->taxDetailsBuilder->expects($this->once())
            ->method('populateWithArray')
            ->with($taxDetailsData)
            ->willReturnSelf();

        $this->taxDetailsBuilder->expects($this->once())->method('setItems')->willReturnSelf();
        $taxDetailsMock = $this->getMock('\Magento\Tax\Api\Data\TaxDetailsInterface');

        $this->taxDetailsBuilder->expects($this->once())->method('create')->willReturn($taxDetailsMock);
        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }
}
