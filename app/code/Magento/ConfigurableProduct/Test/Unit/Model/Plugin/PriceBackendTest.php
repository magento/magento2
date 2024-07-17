<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Plugin\PriceBackend;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceBackendTest extends TestCase
{
    const CLOSURE_VALUE = 'CLOSURE';

    /**
     * @var PriceBackend
     */
    private $priceBackendPlugin;

    /**
     * @var Price|MockObject
     */
    private $priceAttribute;

    /**
     * @var \Closure
     */
    private $closure;

    /**
     * @var Product|MockObject
     */
    private $product;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->priceBackendPlugin = $objectManager->getObject(PriceBackend::class);

        $this->closure = function () {
            return static::CLOSURE_VALUE;
        };
        $this->priceAttribute = $this->getMockBuilder(Price::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeId'])
            ->addMethods(['getPriceType'])
            ->getMock();
    }

    /**
     * @dataProvider aroundValidateDataProvider
     *
     * @param $typeId
     * @param $expectedResult
     */
    public function testAroundValidate($typeId, $expectedResult)
    {
        $this->product->expects(static::once())
            ->method('getTypeId')
            ->willReturn($typeId);
        $result = $this->priceBackendPlugin->aroundValidate(
            $this->priceAttribute,
            $this->closure,
            $this->product
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testAroundValidate
     *
     * @return array
     */
    public static function aroundValidateDataProvider()
    {
        return [
            ['type' => Configurable::TYPE_CODE, 'result' => true],
            ['type' => Type::TYPE_VIRTUAL, 'result' => static::CLOSURE_VALUE],
        ];
    }
}
