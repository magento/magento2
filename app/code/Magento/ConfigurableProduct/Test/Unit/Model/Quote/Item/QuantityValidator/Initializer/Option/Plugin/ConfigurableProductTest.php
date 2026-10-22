<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin\ConfigurableProduct
    as InitializerOptionPlugin;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use PHPUnit\Framework\TestCase;

class ConfigurableProductTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @param array $data
     */
    #[DataProvider('afterGetStockItemDataProvider')]
    public function testAfterGetStockItem(array $data)
    {
        $subjectMock = $this->createMock(
            Option::class
        );

        $quoteItemMock = $this->createPartialMock(
            Item::class,
            ['getProductType']
        );
        $quoteItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn($data['product_type']);

        $stockItemMock = $this->createPartialMockWithReflection(StockItem::class, ['setProductName']);

        $optionMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['getProduct']
        );

        $model = new InitializerOptionPlugin();
        $model->afterGetStockItem($subjectMock, $stockItemMock, $optionMock, $quoteItemMock, 0);
    }

    /**
     * @param string $productType
     * @param float|null $backorders
     * @param float|null $expectedBackorders
     */
    #[DataProvider('afterInitializeDataProvider')]
    public function testAfterInitialize(string $productType, ?float $backorders, ?float $expectedBackorders): void
    {
        $quoteItem = $this->createPartialMock(Item::class, ['getProductType']);
        $quoteItem->expects($this->once())->method('getProductType')->willReturn($productType);
        $result = new DataObject(['item_backorders' => $backorders]);

        $model = new InitializerOptionPlugin();
        $this->assertSame(
            $result,
            $model->afterInitialize(
                $this->createStub(Option::class),
                $result,
                $this->createStub(QuoteItemOption::class),
                $quoteItem
            )
        );
        $this->assertSame($expectedBackorders, $quoteItem->getBackorders());
    }

    /**
     * @return array
     */
    public static function afterInitializeDataProvider(): array
    {
        return [
            'configurable with backorders' => ['configurable', 2.0, 2.0],
            'configurable without backorders' => ['configurable', null, null],
            'not configurable' => ['bundle', 2.0, null],
        ];
    }

    /**
     * @return array
     */
    public static function afterGetStockItemDataProvider()
    {
        return [
            [
                [
                    'product_type' => 'not_configurable',
                    'matcher_method' => 'never',
                ],
            ],
            [
                [
                    'product_type' => 'configurable',
                    'matcher_method' => 'once',
                ]
            ]
        ];
    }
}
