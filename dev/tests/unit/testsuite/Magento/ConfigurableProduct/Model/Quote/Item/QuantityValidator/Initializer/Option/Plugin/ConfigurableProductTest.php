<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item\QuantityValidator\Initializer\Option\Plugin;

class ConfigurableProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $data
     * @dataProvider aroundGetStockItemDataProvider
     */
    public function testAroundGetStockItem(array $data)
    {
        $subjectMock = $this->getMock(
            'Magento\CatalogInventory\Model\Quote\Item\QuantityValidator\Initializer\Option',
            [],
            [],
            '',
            false
        );

        $quoteItemMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item', ['getProductType', '__wakeup'], [], '', false
        );
        $quoteItemMock->expects($this->once())
            ->method('getProductType')
            ->will($this->returnValue($data['product_type']));

        $stockItemMock = $this->getMock(
            'Magento\CatalogInventory\Model\Stock\Item', ['setProductName', '__wakeup'], [], '', false
        );
        $matcherMethod = $data['matcher_method'];
        $stockItemMock->expects($this->$matcherMethod())
            ->method('setProductName');

        $optionMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item\Option', ['getProduct', '__wakeup'], [], '', false
        );

        $proceed = function () use ($stockItemMock) {
            return $stockItemMock;
        };

        $model = new ConfigurableProduct();
        $model->aroundGetStockItem($subjectMock, $proceed, $optionMock, $quoteItemMock, 0);
    }

    /**
     * @return array
     */
    public function aroundGetStockItemDataProvider()
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
