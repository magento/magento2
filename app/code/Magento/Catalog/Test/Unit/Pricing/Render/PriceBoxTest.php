<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Render;

/**
 * Class PriceBoxTest
 */
class PriceBoxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Pricing\Render\PriceBox
     */
    protected $object;

    /**
     * @var \Magento\Framework\Json\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $mathRandom;

    protected function setUp(): void
    {
        $this->jsonHelperMock = $this->createPartialMock(\Magento\Framework\Json\Helper\Data::class, ['jsonEncode']);
        $this->mathRandom = $this->createMock(\Magento\Framework\Math\Random::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject(
            \Magento\Catalog\Pricing\Render\PriceBox::class,
            [
                'jsonHelper' => $this->jsonHelperMock,
                'mathRandom' => $this->mathRandom,
            ]
        );
    }

    public function testJsonEncode()
    {
        $expectedValue = 'string';

        $this->jsonHelperMock->expects($this->once())
            ->method('jsonEncode')
            ->with($this->equalTo($expectedValue))
            ->willReturn($expectedValue);

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
            ->willReturn('test_value');

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
        $product = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['getTypeId', '__wakeup']);

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn($typeCode);

        $this->assertEquals($expected, $this->object->getCanDisplayQty($product));
    }

    /**
     * @return array
     */
    public function getCanDisplayQtyDataProvider()
    {
        return [
            'product is not of type grouped' => ['configurable', true],
            'product is of type grouped' => ['grouped', false]
        ];
    }
}
