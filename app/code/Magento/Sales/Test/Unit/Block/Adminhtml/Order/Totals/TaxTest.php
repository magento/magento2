<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Totals;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Totals\Tax;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\Collection;
use Magento\Tax\Model\Sales\Order\Tax as TaxModel;
use Magento\Tax\Model\Sales\Order\TaxFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Sales\Block\Adminhtml\Order\Totals\Tax
 */
class TaxTest extends TestCase
{
    /**
     * @var array
     */
    private static $calculatedData = [
        'tax' => 'tax',
        'shipping_tax' => 'shipping_tax',
    ];

    /**
     * @var MockObject|Tax
     */
    private $taxMock;

    /**
     * @var Data|MockObject
     */
    private $taxHelperMock;

    /**
     * @var TaxFactory|MockObject
     */
    private $taxOrderFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->taxHelperMock = $this->getMockBuilder(Data::class)
            ->onlyMethods(['getCalculatedTaxes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxOrderFactory = $this->createMock(TaxFactory::class);

        $arguments = $this->getModelArguments(
            ['taxHelper' => $this->taxHelperMock, 'taxOrderFactory' => $this->taxOrderFactory]
        );
        $this->taxMock = $this->getMockBuilder(Tax::class)
            ->setConstructorArgs($arguments)
            ->onlyMethods(['getOrder', 'getSource'])
            ->getMock();
    }

    /**
     * Test method for getFullTaxInfo
     *
     * @param Order|null|\Closure $source
     * @param array $expectedResult
     * @return void
     *
     * @dataProvider getFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfo($source, array $expectedResult): void
    {
        if ($source != null) {
            $source = $source($this);
        }
        $this->taxHelperMock->expects($this->any())
            ->method('getCalculatedTaxes')
            ->willReturn(self::$calculatedData);
        $this->taxMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($source);

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test method for getFullTaxInfo with invoice or creditmemo
     *
     * @param \Closure $source
     * @param array $expectedResult
     * @return void
     *
     * @dataProvider getCreditAndInvoiceFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfoWithCreditAndInvoice($source, array $expectedResult): void
    {
        $source = $source($this);
        $this->taxHelperMock->expects($this->any())
            ->method('getCalculatedTaxes')
            ->willReturn(self::$calculatedData);
        $this->taxMock->expects($this->once())
            ->method('getSource')
            ->willReturn($source);

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test method for getFullTaxInfo when order doesn't have tax
     *
     * @return void
     */
    public function testGetFullTaxInfoOrderWithoutTax(): void
    {
        $this->taxHelperMock->expects($this->once())
            ->method('getCalculatedTaxes')
            ->willReturn(null);

        $orderMock = $this->createMock(Order::class);
        $taxCollection = $this->createMock(Collection::class);
        $taxCollection->expects($this->once())
            ->method('loadByOrder')
            ->with($orderMock)
            ->willReturnSelf();
        $taxCollection->expects($this->once())
            ->method('toArray')
            ->willReturn(['items' => []]);

        $taxOrder = $this->createMock(TaxModel::class);
        $taxOrder->expects($this->once())
            ->method('getCollection')
            ->willReturn($taxCollection);
        $this->taxOrderFactory->expects($this->once())
            ->method('create')
            ->willReturn($taxOrder);

        $invoiceMock = $this->createMock(Invoice::class);
        $this->taxMock->expects($this->once())
            ->method('getSource')
            ->willReturn($invoiceMock);
        $this->taxMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($orderMock);

        $this->assertNull($this->taxMock->getFullTaxInfo());
    }

    /**
     * Provide the tax helper mock as a constructor argument
     *
     * @param array $arguments
     * @return array
     */
    private function getModelArguments(array $arguments): array
    {
        $objectManagerHelper = new ObjectManager($this);

        return $objectManagerHelper->getConstructArguments(Tax::class, $arguments);
    }

    /**
     * Data provider.
     * 1st Case : $source is not an instance of \Magento\Sales\Model\Order
     * 2nd Case : getCalculatedTaxes and getShippingTax return value
     *
     * @return array
     */
    public static function getFullTaxInfoDataProvider(): array
    {
        $salesModelOrderMock = static fn (self $testCase) => $testCase->createMock(Order::class);

        return [
            'source is not an instance of \Magento\Sales\Model\Order' => [null, []],
            'source is an instance of \Magento\Sales\Model\Order and has reasonable data' => [
                $salesModelOrderMock,
                self::$calculatedData,
            ]
        ];
    }

    /**
     * Data provider.
     * 1st Case : $current an instance of \Magento\Sales\Model\Invoice
     * 2nd Case : $current an instance of \Magento\Sales\Model\Creditmemo
     *
     * @return array
     */
    public static function getCreditAndInvoiceFullTaxInfoDataProvider(): array
    {
        $invoiceMock = static fn (self $testCase) => $testCase->createMock(Invoice::class);
        $creditMemoMock = static fn (self $testCase) => $testCase->createMock(Creditmemo::class);

        return [
            'invoice' => [$invoiceMock, self::$calculatedData],
            'creditMemo' => [$creditMemoMock, self::$calculatedData]
        ];
    }
}
