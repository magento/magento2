<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Block\Adminhtml\Items\Price;

class RendererTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Items\Price\Renderer
     */
    protected $renderer;

    /**
     * @var \Magento\Tax\Block\Item\Price\Renderer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemPriceRenderer;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $defaultColumnRenderer;

    protected function setUp(): void
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->itemPriceRenderer = $this->getMockBuilder(\Magento\Tax\Block\Item\Price\Renderer::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'displayPriceInclTax',
                    'displayPriceExclTax',
                    'displayBothPrices',
                    'getTotalAmount',
                    'formatPrice',
                ]
            )
            ->getMock();

        $this->defaultColumnRenderer = $this->getMockBuilder(
            \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn::class
        )->disableOriginalConstructor()
            ->setMethods(['displayPrices'])
            ->getMock();

        $this->renderer = $objectManager->getObject(
            \Magento\Tax\Block\Adminhtml\Items\Price\Renderer::class,
            [
                'itemPriceRenderer' => $this->itemPriceRenderer,
                'defaultColumnRenderer' => $this->defaultColumnRenderer,
            ]
        );
    }

    public function testDisplayPriceInclTax()
    {
        $flag = false;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayPriceInclTax')
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayPriceInclTax());
    }

    public function testDisplayPriceExclTax()
    {
        $flag = true;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayPriceExclTax')
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayPriceExclTax());
    }

    public function testDisplayBothPrices()
    {
        $flag = true;
        $this->itemPriceRenderer->expects($this->once())
            ->method('displayBothPrices')
            ->willReturn($flag);

        $this->assertEquals($flag, $this->renderer->displayBothPrices());
    }

    public function testDisplayPrices()
    {
        $basePrice = 3;
        $price = 4;
        $display = "$3 [L4]";

        $this->defaultColumnRenderer->expects($this->once())
            ->method('displayPrices')
            ->with($basePrice, $price)
            ->willReturn($display);

        $this->assertEquals($display, $this->renderer->displayPrices($basePrice, $price));
    }

    public function testFormatPrice()
    {
        $price = 4;
        $display = "$3";

        $this->itemPriceRenderer->expects($this->once())
            ->method('formatPrice')
            ->with($price)
            ->willReturn($display);

        $this->assertEquals($display, $this->renderer->formatPrice($price));
    }

    public function testGetTotalAmount()
    {
        $totalAmount = 10;
        $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemPriceRenderer->expects($this->once())
            ->method('getTotalAmount')
            ->with($itemMock)
            ->willReturn($totalAmount);

        $this->assertEquals($totalAmount, $this->renderer->getTotalAmount($itemMock));
    }
}
