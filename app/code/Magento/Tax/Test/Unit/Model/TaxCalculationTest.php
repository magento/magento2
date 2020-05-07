<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\Data\QuoteDetailsInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Api\Data\TaxDetailsInterface;
use Magento\Tax\Api\Data\TaxDetailsInterfaceFactory;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Api\Data\TaxDetailsItemInterfaceFactory;
use Magento\Tax\Api\TaxCalculationInterface;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Calculation\CalculatorFactory;
use Magento\Tax\Model\Calculation\TotalBaseCalculator;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\TaxCalculation;
use Magento\Tax\Model\TaxDetails\TaxDetails;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxCalculationTest extends TestCase
{
    /**
     * @var TaxCalculationInterface
     */
    private $taxCalculationService;

    /**
     * @var MockObject
     */
    private $taxDetailsItemDataObjectFactory;

    /**
     * @var MockObject
     */
    private $taxDetailsDataObjectFactory;

    /**
     * @var MockObject
     */
    private $storeManager;

    /**
     * @var MockObject
     */
    private $calculatorFactory;

    /**
     * @var MockObject
     */
    private $calculationTool;

    /**
     * @var MockObject
     */
    private $configMock;

    /**
     * @var MockObject
     */
    private $taxClassManagementMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    protected function setUp(): void
    {
        $this->calculationTool = $this->createMock(Calculation::class);
        $this->calculatorFactory = $this->createMock(CalculatorFactory::class);
        $this->configMock = $this->createMock(Config::class);
        $this->taxDetailsDataObjectFactory = $this->createPartialMock(
            TaxDetailsInterfaceFactory::class,
            ['create']
        );
        $this->taxDetailsItemDataObjectFactory = $this->createMock(
            TaxDetailsItemInterfaceFactory::class
        );
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->dataObjectHelperMock = $this->getMockBuilder(DataObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->taxClassManagementMock = $this->getMockForAbstractClass(TaxClassManagementInterface::class);

        $objectManager = new ObjectManager($this);
        $this->taxCalculationService = $objectManager->getObject(
            TaxCalculation::class,
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

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setProductClassId'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $rateRequestMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setProductClassId'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $quoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $quoteDetailsMock->expects($this->once())->method('getItems')->willReturn(null);

        $taxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
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
            TaxDetails::KEY_SUBTOTAL => 0.0,
            TaxDetails::KEY_TAX_AMOUNT => 0.0,
            TaxDetails::KEY_DISCOUNT_TAX_COMPENSATION_AMOUNT => 0.0,
            TaxDetails::KEY_APPLIED_TAXES => [],
            TaxDetails::KEY_ITEMS => [],
        ];

        $quoteDetailsMock = $this->getMockForAbstractClass(QuoteDetailsInterface::class);

        $storeMock = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $billAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $shipAddressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $taxClassKeyMock = $this->getMockForAbstractClass(TaxClassKeyInterface::class);

        $quoteDetailsItemMock = $this->getMockForAbstractClass(QuoteDetailsItemInterface::class);
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

        $calculatorMock = $this->createMock(TotalBaseCalculator::class);
        $this->calculatorFactory->expects($this->once())
            ->method('create')
            ->with($algorithm, $storeId, $billAddressMock, $shipAddressMock, $taxClassId, $customerId)
            ->willReturn($calculatorMock);

        $taxDetailsMock = $this->getMockForAbstractClass(TaxDetailsItemInterface::class);
        $calculatorMock->expects($this->once())->method('calculate')->willReturn($taxDetailsMock);

        $taxDetailsMock = $this->getMockForAbstractClass(TaxDetailsInterface::class);
        $this->taxDetailsDataObjectFactory->expects($this->once())->method('create')->willReturn($taxDetailsMock);
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($taxDetailsMock, $taxDetailsData)
            ->willReturnSelf();

        $this->assertEquals($taxDetailsMock, $this->taxCalculationService->calculateTax($quoteDetailsMock));
    }
}
