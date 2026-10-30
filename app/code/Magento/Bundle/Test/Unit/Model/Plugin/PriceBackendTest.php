<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Plugin\PriceBackend;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceBackendTest extends TestCase
{
    use MockCreationTrait;

    private const CLOSURE_VALUE = 'CLOSURE';

    /** @var  PriceBackend */
    private $priceBackendPlugin;

    /** @var  MockObject */
    private $priceAttributeMock;

    /** @var  Closure */
    private $closure;

    /** @var  Product|MockObject */
    private $productMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->priceBackendPlugin = $objectManager->getObject(PriceBackend::class);

        $this->closure = function () {
            return static::CLOSURE_VALUE;
        };
        $this->priceAttributeMock = $this->createMock(
            \Magento\Catalog\Model\Product\Attribute\Backend\Price::class
        );
        $this->productMock = $this->createPartialMockWithReflection(
            Product::class,
            ['getTypeId', 'getPriceType']
        );
    }

    /**
     *
     * @param $typeId
     * @param $priceType
     * @param $expectedResult
     */
    #[DataProvider('aroundValidateDataProvider')]
    public function testAroundValidate($typeId, $priceType, $expectedResult)
    {
        // Configure mock with getter methods
        $this->productMock->method('getTypeId')->willReturn($typeId);
        $this->productMock->method('getPriceType')->willReturn($priceType);

        $result = $this->priceBackendPlugin->aroundValidate(
            $this->priceAttributeMock,
            $this->closure,
            $this->productMock
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
            ['typeId' => Type::TYPE_SIMPLE, 'priceType' => Price::PRICE_TYPE_FIXED,
                'expectedResult' => static::CLOSURE_VALUE],
            ['typeId' => Type::TYPE_SIMPLE, 'priceType' => Price::PRICE_TYPE_DYNAMIC,
                'expectedResult' => static::CLOSURE_VALUE],
            ['typeId' => Type::TYPE_BUNDLE, 'priceType' => Price::PRICE_TYPE_FIXED,
                'expectedResult' => static::CLOSURE_VALUE],
            ['typeId' => Type::TYPE_BUNDLE, 'priceType' => Price::PRICE_TYPE_DYNAMIC,
                'expectedResult' => true],
            ['typeId' => Type::TYPE_VIRTUAL, 'priceType' => Price::PRICE_TYPE_FIXED,
                'expectedResult' => static::CLOSURE_VALUE],
            ['typeId' => Type::TYPE_VIRTUAL, 'priceType' => Price::PRICE_TYPE_DYNAMIC,
                'expectedResult' => static::CLOSURE_VALUE],
        ];
    }
}
