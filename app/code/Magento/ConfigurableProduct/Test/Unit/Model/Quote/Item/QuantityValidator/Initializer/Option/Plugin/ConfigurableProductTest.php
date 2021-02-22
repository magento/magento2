<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

use Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin\ConfigurableProduct
    as InitializerOptionPlugin;

class ConfigurableProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param array $data
     * @dataProvider afterGetStockItemDataProvider
     */
    public function testAfterGetStockItem(array $data)
    {
        $subjectMock = $this->createMock(
            \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option::class
        );

        $quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProductType', '__wakeup']
        );
        $quoteItemMock->expects($this->once())
            ->method('getProductType')
            ->willReturn($data['product_type']);

        $stockItemMock = $this->createPartialMock(
            \Magento\CatalogInventory\Model\Stock\Item::class,
            ['setProductName', '__wakeup']
        );
        $matcherMethod = $data['matcher_method'];
        $stockItemMock->expects($this->$matcherMethod())
            ->method('setProductName');

        $optionMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item\Option::class,
            ['getProduct', '__wakeup']
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
