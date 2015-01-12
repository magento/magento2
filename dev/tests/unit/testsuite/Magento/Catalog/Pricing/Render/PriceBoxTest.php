<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Pricing\Render;

/**
 * Class PriceBoxTest
 */
class PriceBoxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Render\PriceBox
     */
    protected $object;

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $coreHelper;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $mathRandom;

    protected function setUp()
    {
        $this->coreHelper = $this->getMock('Magento\Core\Helper\Data', ['jsonEncode'], [], '', false);
        $this->mathRandom = $this->getMock('Magento\Framework\Math\Random', [], [], '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            'Magento\Catalog\Pricing\Render\PriceBox',
            [
                'coreDataHelper' => $this->coreHelper,
                'mathRandom' => $this->mathRandom,
            ]
        );
    }

    public function testJsonEncode()
    {
        $expectedValue = 'string';

        $this->coreHelper->expects($this->once())
            ->method('jsonEncode')
            ->with($this->equalTo($expectedValue))
            ->will($this->returnValue($expectedValue));

        $result = $this->object->jsonEncode($expectedValue);

        $this->assertEquals($expectedValue, $result);
    }

    public function testGetRandomString()
    {
        $expectedValue = 20;

        $expectedTestValue = 'test_value';
        $this->mathRandom->expects($this->once())
            ->method('getRandomString')
            ->with($this->equalTo($expectedValue))
            ->will($this->returnValue('test_value'));

        $result = $this->object->getRandomString($expectedValue);

        $this->assertEquals($expectedTestValue, $result);
    }

    /**
     * test for method getCanDisplayQty
     *
     * @param string $typeCode
     * @param bool $expected
     * @dataProvider getCanDisplayQtyDataProvider
     */
    public function testGetCanDisplayQty($typeCode, $expected)
    {
        $product = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', '__wakeup'], [], '', false);

        $product->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue($typeCode));

        $this->assertEquals($expected, $this->object->getCanDisplayQty($product));
    }

    public function getCanDisplayQtyDataProvider()
    {
        return [
            'product is not of type grouped' => ['configurable', true],
            'product is of type grouped' => ['grouped', false]
        ];
    }
}
