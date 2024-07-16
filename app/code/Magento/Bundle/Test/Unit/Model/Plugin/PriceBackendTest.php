<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use Magento\Bundle\Model\Plugin\PriceBackend;
use Magento\Bundle\Model\Product\Price;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceBackendTest extends TestCase
{
    private const CLOSURE_VALUE = 'CLOSURE';

    /** @var  PriceBackend */
    private $priceBackendPlugin;

    /** @var  MockObject */
    private $priceAttributeMock;

    /** @var  \Closure */
    private $closure;

    /** @var  MockObject */
    private $productMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->priceBackendPlugin = $objectManager->getObject(PriceBackend::class);

        $this->closure = function () {
            return static::CLOSURE_VALUE;
        };
        $this->priceAttributeMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Attribute\Backend\Price::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->addMethods(['getPriceType'])
            ->onlyMethods(['getTypeId', '__wakeUp'])
            ->getMock();
    }

    /**
     * @dataProvider aroundValidateDataProvider
     *
     * @param $typeId
     * @param $priceType
     * @param $expectedResult
     */
    public function testAroundValidate($typeId, $priceType, $expectedResult)
    {
        $this->productMock->expects($this->any())->method('getTypeId')->willReturn($typeId);
        $this->productMock->expects($this->any())->method('getPriceType')->willReturn($priceType);
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
            ['type' => Type::TYPE_SIMPLE, 'priceType' => Price::PRICE_TYPE_FIXED, 'result' => static::CLOSURE_VALUE],
            ['type' => Type::TYPE_SIMPLE, 'priceType' => Price::PRICE_TYPE_DYNAMIC, 'result' => static::CLOSURE_VALUE],
            ['type' => Type::TYPE_BUNDLE, 'priceType' => Price::PRICE_TYPE_FIXED, 'result' => static::CLOSURE_VALUE],
            ['type' => Type::TYPE_BUNDLE, 'priceType' => Price::PRICE_TYPE_DYNAMIC, 'result' => true],
            ['type' => Type::TYPE_VIRTUAL, 'priceType' => Price::PRICE_TYPE_FIXED, 'result' => static::CLOSURE_VALUE],
            ['type' => Type::TYPE_VIRTUAL, 'priceType' => Price::PRICE_TYPE_DYNAMIC, 'result' => static::CLOSURE_VALUE],
        ];
    }
}
