<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Sales\Block\Adminhtml\Order\Totals\TaxTest
 */
namespace Magento\Sales\Test\Unit\Block\Adminhtml\Order\Totals;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Block\Adminhtml\Order\Totals\Tax;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Invoice;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxTest extends TestCase
{
    /** @var  MockObject|Tax */
    private $taxMock;

    protected function setUp(): void
    {
        $getCalculatedTax = [
            'tax' => 'tax',
            'shipping_tax' => 'shipping_tax',
        ];
        $taxHelperMock = $this->getMockBuilder(Data::class)
            ->setMethods(['getCalculatedTaxes'])
            ->disableOriginalConstructor()
            ->getMock();
        $taxHelperMock->expects($this->any())
            ->method('getCalculatedTaxes')
            ->willReturn($getCalculatedTax);

        $this->taxMock = $this->getMockBuilder(Tax::class)
            ->setConstructorArgs($this->_getConstructArguments($taxHelperMock))
            ->setMethods(['getOrder', 'getSource'])
            ->getMock();
    }

    /**
     * Test method for getFullTaxInfo
     *
     * @param Order $source
     * @param array $getCalculatedTax
     * @param array $getShippingTax
     * @param array $expectedResult
     *
     * @dataProvider getFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfo($source, $expectedResult)
    {
        $this->taxMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($source);

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Test method for getFullTaxInfo with invoice or creditmemo
     *
     * @param Invoice|Creditmemo $source
     * @param array $expectedResult
     *
     * @dataProvider getCreditAndInvoiceFullTaxInfoDataProvider
     */
    public function testGetFullTaxInfoWithCreditAndInvoice(
        $source,
        $expectedResult
    ) {
        $this->taxMock->expects($this->once())
            ->method('getSource')
            ->willReturn($source);

        $actualResult = $this->taxMock->getFullTaxInfo();
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * Provide the tax helper mock as a constructor argument
     *
     * @param $taxHelperMock
     * @return array
     */
    protected function _getConstructArguments($taxHelperMock)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getConstructArguments(
            Tax::class,
            ['taxHelper' => $taxHelperMock]
        );
    }

    /**
     * Data provider.
     * 1st Case : $source is not an instance of \Magento\Sales\Model\Order
     * 2nd Case : getCalculatedTaxes and getShippingTax return value
     *
     * @return array
     */
    public function getFullTaxInfoDataProvider()
    {
        $salesModelOrderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        return [
            'source is not an instance of \Magento\Sales\Model\Order' => [null, []],
            'source is an instance of \Magento\Sales\Model\Order and has reasonable data' => [
                $salesModelOrderMock,
                [
                    'tax' => 'tax',
                    'shipping_tax' => 'shipping_tax',
                ],
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
    public function getCreditAndInvoiceFullTaxInfoDataProvider()
    {
        $invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $creditMemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expected = [
            'tax' => 'tax',
            'shipping_tax' => 'shipping_tax',
        ];
        return [
            'invoice' => [$invoiceMock, $expected],
            'creditMemo' => [$creditMemoMock, $expected]
        ];
    }
}
