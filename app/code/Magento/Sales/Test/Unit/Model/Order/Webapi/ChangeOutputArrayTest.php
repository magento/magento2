<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Webapi;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order\Webapi\ChangeOutputArray;

/**
 * Test for \Magento\Sales\Model\Order\Webapi\ChangeOutputArray class
 */
class ChangeOutputArrayTest extends TestCase
{
    /**
     * @var ChangeOutputArray
     */
    private $changeOutputArray;

    /**
     * @var DefaultColumn|MockObject
     */
    private $priceRendererMock;

    /**
     * @var DefaultRenderer|MockObject
     */
    private $defaultRendererMock;

    protected function setUp(): void
    {
        $this->priceRendererMock = $this->createMock(DefaultColumn::class);
        $this->defaultRendererMock = $this->createMock(DefaultRenderer::class);
        $this->changeOutputArray = new ChangeOutputArray($this->priceRendererMock, $this->defaultRendererMock);
    }

    /**
     * @dataProvider negativeTotals
     */
    public function testNoNegativeValue($totals, $expected)
    {
        $this->priceRendererMock->expects($this->once())
            ->method('getTotalAmount')
            ->willReturn($totals['totalAmount']);
        $this->priceRendererMock->expects($this->once())
            ->method('getBaseTotalAmount')
            ->willReturn($totals['baseTotalAmount']);
        $this->defaultRendererMock->expects($this->once())
            ->method('getTotalAmount')
            ->willReturn($totals['totalAmountIncTax']);
        $dataObjectMock = $this->getMockForAbstractClass(OrderItemInterface::class);
        $dataObjectMock->expects($this->once())
            ->method('getBaseRowTotal')
            ->willReturn($totals['baseRowTotal']);
        $dataObjectMock->expects($this->once())
            ->method('getBaseTaxAmount')
            ->willReturn($totals['baseTaxAmount']);
        $dataObjectMock->expects($this->once())
            ->method('getBaseDiscountTaxCompensationAmount')
            ->willReturn($totals['baseDiscountTaxCompensationAmount']);
        $dataObjectMock->expects($this->once())
            ->method('getBaseWeeeTaxAppliedAmount')
            ->willReturn($totals['baseWeeeTaxAppliedAmount']);
        $dataObjectMock->expects($this->once())
            ->method('getBaseDiscountAmount')
            ->willReturn($totals['baseDiscountAmount']);
        $this->assertEquals($expected, $this->changeOutputArray->execute($dataObjectMock, []));
    }

    /**
     * Data provider for testNoNegativeValue
     * @return array
     */
    public static function negativeTotals()
    {
        return [
            [
                'totals' => [
                    'totalAmount' => -1.14,
                    'baseTotalAmount' => -1.14,
                    'totalAmountIncTax' => -8.8817841970013E-16,
                    'baseRowTotal' => 4.7600,
                    'baseTaxAmount' => 0.0000,
                    'baseDiscountTaxCompensationAmount' => 1.1400,
                    'baseWeeeTaxAppliedAmount' => null,
                    'baseDiscountAmount' => 5.9000
                ],
                'expected' => [
                    OrderItemInterface::ROW_TOTAL => 0,
                    OrderItemInterface::BASE_ROW_TOTAL => 0,
                    OrderItemInterface::ROW_TOTAL_INCL_TAX => 0,
                    OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX => 0
                ]
            ]
        ];
    }
}
