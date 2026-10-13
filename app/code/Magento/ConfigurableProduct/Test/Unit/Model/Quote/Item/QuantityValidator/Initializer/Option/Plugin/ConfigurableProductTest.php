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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Quote\Model\Quote\Item;
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
