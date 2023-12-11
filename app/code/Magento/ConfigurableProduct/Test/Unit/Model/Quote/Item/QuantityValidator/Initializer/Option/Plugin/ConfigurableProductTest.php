<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option;
use Magento\CatalogInventory\Model\Stock\Item as StockItemModel;
use Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin\ConfigurableProduct
    as InitializerOptionPlugin;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\TestCase;

class ConfigurableProductTest extends TestCase
{
    /**
     * @param array $data
     * @dataProvider afterGetStockItemDataProvider
     */
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

        $stockItemMock = $this->getMockBuilder(StockItemModel::class)
            ->addMethods(['setProductName'])
            ->disableOriginalConstructor()
            ->getMock();
        $matcherMethod = $data['matcher_method'];
        $stockItemMock->expects($this->$matcherMethod())
            ->method('setProductName');

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
    public function afterGetStockItemDataProvider()
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
