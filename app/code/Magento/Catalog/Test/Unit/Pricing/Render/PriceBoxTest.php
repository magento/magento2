<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Render;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render\PriceBox;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceBoxTest extends TestCase
{
    /**
     * @var PriceBox
     */
    protected $object;

    /**
     * @var Data|MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var Random|MockObject
     */
    protected $mathRandom;

    protected function setUp(): void
    {
        $this->jsonHelperMock = $this->createPartialMock(Data::class, ['jsonEncode']);
        $this->mathRandom = $this->createMock(Random::class);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject(
            PriceBox::class,
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
            ->with($expectedValue)
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
            ->with($expectedValue)
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
        $product = $this->createPartialMock(Product::class, ['getTypeId']);

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn($typeCode);

        $this->assertEquals($expected, $this->object->getCanDisplayQty($product));
    }

    /**
     * @return array
     */
    public static function getCanDisplayQtyDataProvider()
    {
        return [
            'product is not of type grouped' => ['configurable', true],
            'product is of type grouped' => ['grouped', false]
        ];
    }
}
