<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Product\View;

use Magento\Catalog\Block\Product\View\Price;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Price block
 *
 * @covers \Magento\Catalog\Block\Product\View\Price
 */
class PriceTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var Price
     */
    private Price $block;

    /**
     * @var Registry|MockObject
     */
    private MockObject $registryMock;

    /**
     * @var Product|MockObject
     */
    private MockObject $productMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->objectManager->prepareObjectManager();

        $this->registryMock = $this->createMock(Registry::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFormattedPrice'])
            ->getMock();

        $contextMock = $this->createMock(Context::class);

        $this->block = $this->objectManager->getObject(
            Price::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        unset($this->block);
    }

    /**
     * Test getPrice returns formatted price from registry product
     *
     * @covers \Magento\Catalog\Block\Product\View\Price::getPrice
     * @param string|float $formattedPrice
     * @return void
     */
    #[DataProvider('priceDataProvider')]
    public function testGetPriceReturnsFormattedPriceFromProduct(string|float $formattedPrice): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->once())
            ->method('getFormattedPrice')
            ->willReturn($formattedPrice);

        $result = $this->block->getPrice();

        $this->assertSame($formattedPrice, $result);
    }

    /**
     * Data provider for price test scenarios
     *
     * @return array
     */
    public static function priceDataProvider(): array
    {
        return [
            'string formatted price' => [
                'formattedPrice' => '$99.99'
            ],
            'float price value' => [
                'formattedPrice' => 99.99
            ],
            'zero price' => [
                'formattedPrice' => 0.00
            ],
            'empty string price' => [
                'formattedPrice' => ''
            ]
        ];
    }

    /**
     * Test getPrice throws error when product is not in registry
     *
     * @covers \Magento\Catalog\Block\Product\View\Price::getPrice
     * @return void
     */
    public function testGetPriceThrowsErrorWhenProductIsNull(): void
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn(null);

        $this->expectException(\Error::class);

        $this->block->getPrice();
    }
}
