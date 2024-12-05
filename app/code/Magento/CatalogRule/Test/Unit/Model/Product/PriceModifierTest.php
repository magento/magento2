<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\CatalogRule\Model\Product\PriceModifier;
use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRule\Model\RuleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceModifierTest extends TestCase
{
    /**
     * @var PriceModifier
     */
    protected $priceModifier;

    /**
     * @var MockObject
     */
    protected $ruleFactoryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $ruleMock;

    protected function setUp(): void
    {
        $this->ruleFactoryMock = $this->createPartialMock(RuleFactory::class, ['create']);
        $this->productMock = $this->createMock(Product::class);
        $this->ruleMock = $this->createMock(Rule::class);
        $this->priceModifier = new PriceModifier($this->ruleFactoryMock);
    }

    /**
     * @param int|null $resultPrice
     * @param int $expectedPrice
     * @dataProvider modifyPriceDataProvider
     */
    public function testModifyPriceIfPriceExists($resultPrice, $expectedPrice)
    {
        $this->ruleFactoryMock->expects($this->once())->method('create')->willReturn($this->ruleMock);
        $this->ruleMock->expects(
            $this->once()
        )->method(
            'calcProductPriceRule'
        )->with(
            $this->productMock,
            100
        )->willReturn(
            $resultPrice
        );
        $this->assertEquals($expectedPrice, $this->priceModifier->modifyPrice(100, $this->productMock));
    }

    /**
     * @return array
     */
    public static function modifyPriceDataProvider()
    {
        return ['resulted_price_exists' => [150, 150], 'resulted_price_not_exists' => [null, 100]];
    }

    public function testModifyPriceIfPriceNotExist()
    {
        $this->ruleFactoryMock->expects($this->never())->method('create');
        $this->assertNull($this->priceModifier->modifyPrice(null, $this->productMock));
    }
}
