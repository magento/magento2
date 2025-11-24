<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use PHPUnit\Framework\Attributes\DataProvider;
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
    private const CLOSURE_VALUE = 'CLOSURE';

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
        $this->priceAttribute = $this->createMock(Price::class);
        $this->product = $this->createPartialMock(Product::class, ['getTypeId']);
    }

    /**
     *
     * @param $typeId
     * @param $expectedResult
     */
    #[DataProvider('aroundValidateDataProvider')]
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
            ['typeId' => Configurable::TYPE_CODE, 'expectedResult' => true],
            ['typeId' => Type::TYPE_VIRTUAL, 'expectedResult' => static::CLOSURE_VALUE],
        ];
    }
}
