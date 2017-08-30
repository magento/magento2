<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

class ItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param string $options
     * @param array $expectedData
     * @dataProvider getProductOptionsDataProvider
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
            [
                '{"option1":1,"option2":2}',
                ["option1" => 1, "option2" => 2]
            ],
            [
                ["option1" => 1, "option2" => 2],
                ["option1" => 1, "option2" => 2]
            ],
        ];
    }
}
