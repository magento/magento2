<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Catalog\Model\Product\Type;
use Magento\ConfigurableProduct\Model\Plugin\PriceBackend;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PriceBackendTest
 */
class PriceBackendTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
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
            ->setMethods(['getTypeId', 'getPriceType', '__wakeUp'])
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
    public function aroundValidateDataProvider()
    {
        return [
            ['type' => Configurable::TYPE_CODE, 'result' => true],
            ['type' => Type::TYPE_VIRTUAL, 'result' => static::CLOSURE_VALUE],
        ];
    }
}
