<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

/**
 * Item test class.
 */
class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Sales\Model\Order\Item::getProductOptions
     * @dataProvider getProductOptionsDataProvider
     * @param string $options
     * @param array $expectedData
     */
    public function testGetProductOptions($options, $expectedData)
    {
        $model = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Sales\Model\Order\Item::class);
        $model->setData('product_options', $options);
        $this->assertEquals($expectedData, $model->getProductOptions());
    }

    /**
     * @return array
     */
    public function getProductOptionsDataProvider()
    {
        return [
            // Variation #1
            [
                // $options
                '{"option1":1,"option2":2}',
                //$expectedData
                ["option1" => 1, "option2" => 2]
            ],
            // Variation #2
            [
                // $options
                'a:2:{s:7:"option1";i:1;s:7:"option2";i:2;}',
                //$expectedData
                null
            ],
            // Variation #3
            [
                // $options
                ["option1" => 1, "option2" => 2],
                //$expectedData
                ["option1" => 1, "option2" => 2]
            ],
        ];
    }
}
