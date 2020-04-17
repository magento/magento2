<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Model\ResourceModel\Link as LinkResourceModel;
use Magento\Downloadable\Pricing\Price\LinkPrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Amount\Base;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LinkPriceTest extends TestCase
{
    /**
     * @var LinkPrice
     */
    protected $linkPrice;

    /**
     * @var Base|MockObject
     */
    protected $amountMock;

    /**
     * @var Product|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Calculator|MockObject
     */
    protected $calculatorMock;

    /**
     * @var LinkResourceModel|MockObject
     */
    protected $linkMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->amountMock = $this->createMock(Base::class);
        $this->calculatorMock = $this->createMock(Calculator::class);
        $this->linkMock = $this->createPartialMock(
            \Magento\Downloadable\Model\Link::class,
            ['getPrice', 'getProduct', '__wakeup']
        );

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->linkPrice = new LinkPrice(
            $this->saleableItemMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock
        );
    }

    public function testGetLinkAmount()
    {
        $amount = 100;
        $convertedAmount = 50;

        $this->linkMock->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($amount));
        $this->linkMock->expects($this->once())
            ->method('getProduct')
            ->will($this->returnValue($this->saleableItemMock));
        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndRound')
            ->with($amount)
            ->will($this->returnValue($convertedAmount));
        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($convertedAmount, $this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($convertedAmount));

        $result = $this->linkPrice->getLinkAmount($this->linkMock);
        $this->assertEquals($convertedAmount, $result);
    }
}
