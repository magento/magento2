<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxCalculationTest extends \PHPUnit\Framework\TestCase
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
        $this->calculationTool = $this->createMock(\Magento\Tax\Model\Calculation::class);
        $this->calculatorFactory = $this->createMock(\Magento\Tax\Model\Calculation\CalculatorFactory::class);
        $this->configMock = $this->createMock(\Magento\Tax\Model\Config::class);
        $this->taxDetailsDataObjectFactory = $this->createPartialMock(
            \Magento\Tax\Api\Data\TaxDetailsInterfaceFactory::class,
            ['create']
        );
        $this->taxDetailsItemDataObjectFactory = $this->createMock(
            \Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory::class
        );
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->dataObjectHelperMock = $this->getMockBuilder(\Magento\Framework\Api\DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxClassManagementMock = $this->createMock(\Magento\Tax\Api\TaxClassManagementInterface::class);

        $objectManager = new ObjectManager($this);
        $this->taxCalculationService = $objectManager->getObject(
            \Magento\Tax\Model\TaxCalculation::class,
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

        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId']);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['setProductClassId']);
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

        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId']);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->createPartialMock(\Magento\Framework\DataObject::class, ['setProductClassId']);
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
        $quoteDetailsMock = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsInterface::class);

        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId']);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $quoteDetailsMock->expects($this->once())->method('getItems')->willReturn(null);

        $taxDetailsMock = $this->createMock(\Magento\Tax\Api\Data\TaxDetailsInterface::class);
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

        $quoteDetailsMock = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsInterface::class);

        $storeMock = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getStoreId']);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $billAddressMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $shipAddressMock = $this->createMock(\Magento\Customer\Api\Data\AddressInterface::class);
        $taxClassKeyMock = $this->createMock(\Magento\Tax\Api\Data\TaxClassKeyInterface::class);

        $quoteDetailsItemMock = $this->createMock(\Magento\Tax\Api\Data\QuoteDetailsItemInterface::class);
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

        $calculatorMock = $this->createMock(\Magento\Tax\Model\Calculation\TotalBaseCalculator::class);
        $this->calculatorFactory->expects($this->once())
            ->method('create')
            ->with($algorithm, $storeId, $billAddressMock, $shipAddressMock, $taxClassId, $customerId)
            ->willReturn($calculatorMock);

        $taxDetailsMock = $this->createMock(\Magento\Tax\Api\Data\TaxDetailsItemInterface::class);
        $calculatorMock->expects($this->once())->method('calculate')->willReturn($taxDetailsMock);

        $taxDetailsMock = $this->createMock(\Magento\Tax\Api\Data\TaxDetailsInterface::class);
        $this->taxDetailsDataObjectFactory->expects($this->once())->method('create')->willReturn($taxDetailsMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($taxDetailsMock, $taxDetailsData)
            ->willReturnSelf();

        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }
}
