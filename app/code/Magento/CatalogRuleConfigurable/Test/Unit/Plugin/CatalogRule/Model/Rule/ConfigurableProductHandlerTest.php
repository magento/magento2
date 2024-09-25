<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Unit\Plugin\CatalogRule\Model\Rule;

use Magento\CatalogRule\Model\Rule;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler
 */
class ConfigurableProductHandlerTest extends TestCase
{
    /**
     * @var ConfigurableProductHandler
     */
    private $configurableProductHandler;

    /**
     * @var Configurable|MockObject
     */
    private $configurableMock;

    /**
     * @var ConfigurableProductsProvider|MockObject
     */
    private $configurableProductsProviderMock;

    /** @var Rule|MockObject */
    private $ruleMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->configurableMock = $this->createPartialMock(
            Configurable::class,
            ['getChildrenIds', 'getParentIdsByChild']
        );
        $this->configurableProductsProviderMock = $this->createPartialMock(
            ConfigurableProductsProvider::class,
            ['getIds']
        );
        $this->ruleMock = $this->createMock(Rule::class);

        $this->configurableProductHandler = new ConfigurableProductHandler(
            $this->configurableMock,
            $this->configurableProductsProviderMock
        );
    }

    /**
     * @return void
     */
    public function testAroundGetMatchingProductIdsWithSimpleProduct()
    {
        $this->configurableProductsProviderMock->expects($this->once())->method('getIds')->willReturn([]);
        $this->configurableMock->expects($this->never())->method('getChildrenIds');
        $this->ruleMock->expects($this->never())
            ->method('setProductsFilter');

        $productIds = ['product' => 'valid results'];
        $this->assertEquals(
            $productIds,
            $this->configurableProductHandler->aroundGetMatchingProductIds(
                $this->ruleMock,
                function () {
                    return ['product' => 'valid results'];
                }
            )
        );
    }

    /**
     * @return void
     */
    public function testAroundGetMatchingProductIdsWithConfigurableProduct()
    {
        $this->configurableProductsProviderMock->expects($this->once())->method('getIds')
            ->willReturn(['conf1', 'conf2']);
        $this->configurableMock->expects($this->any())->method('getChildrenIds')->willReturnMap([
            ['conf1', true, [ 0 => ['simple1']]],
            ['conf2', true, [ 0 => ['simple1', 'simple2']]],
        ]);
        $this->ruleMock->expects($this->never())
            ->method('setProductsFilter');

        $this->assertEquals(
            [
                'simple1' => [
                    0 => true,
                    1 => true,
                    3 => true,
                ],
                'simple2' => [
                    3 => true,
                ]
            ],
            $this->configurableProductHandler->aroundGetMatchingProductIds(
                $this->ruleMock,
                function () {
                    return [
                        'conf1' => [
                            0 => true,
                            1 => true,
                        ],
                        'conf2' => [
                            0 => false,
                            1 => false,
                            3 => true,
                            4 => false,
                        ],
                    ];
                }
            )
        );
    }

    /**
     * @param array $productsFilter
     * @param array $expectedProductsFilter
     * @param array $matchingProductIds
     * @param array $expectedMatchingProductIds
     * @return void
     * @dataProvider aroundGetMatchingProductIdsDataProvider
     */
    public function testAroundGetMatchingProductIdsWithProductsFilter(
        array $productsFilter,
        array $expectedProductsFilter,
        array $matchingProductIds,
        array $expectedMatchingProductIds
    ): void {
        $configurableProducts = [
            'conf1' => ['simple11', 'simple12'],
            'conf2' => ['simple21', 'simple22'],
        ];
        $this->configurableProductsProviderMock->method('getIds')
            ->willReturnCallback(
                function ($ids) use ($configurableProducts) {
                    return array_intersect($ids, array_keys($configurableProducts));
                }
            );
        $this->configurableMock->method('getChildrenIds')
            ->willReturnCallback(
                function ($id) use ($configurableProducts) {
                    return [0 => $configurableProducts[$id] ?? []];
                }
            );

        $this->configurableMock->method('getParentIdsByChild')
            ->willReturnCallback(
                function ($ids) use ($configurableProducts) {
                    $result = [];
                    foreach ($configurableProducts as $configurableProduct => $childProducts) {
                        if (array_intersect($ids, $childProducts)) {
                            $result[] = $configurableProduct;
                        }
                    }
                    return $result;
                }
            );

        $this->ruleMock->method('getProductsFilter')
            ->willReturn($productsFilter);

        $this->ruleMock->expects($this->once())
            ->method('setProductsFilter')
            ->willReturn($expectedProductsFilter);

        $this->assertEquals(
            $expectedMatchingProductIds,
            $this->configurableProductHandler->aroundGetMatchingProductIds(
                $this->ruleMock,
                function () use ($matchingProductIds) {
                    return $matchingProductIds;
                }
            )
        );
    }

    /**
     * @return array[]
     */
    public static function aroundGetMatchingProductIdsDataProvider(): array
    {
        return [
            [
                ['simple1',],
                ['simple1',],
                ['simple1' => [1 => false]],
                ['simple1' => [1 => false],],
            ],
            [
                ['simple11',],
                ['simple11', 'conf1',],
                ['simple11' => [1 => false], 'conf1' => [1 => true],],
                ['simple11' => [1 => true],],
            ],
            [
                ['simple11', 'simple12',],
                ['simple11', 'conf1',],
                ['simple11' => [1 => false], 'conf1' => [1 => true],],
                ['simple11' => [1 => true], 'simple12' => [1 => true],],
            ],
            [
                ['conf1', 'simple11', 'simple12'],
                ['conf1', 'simple11', 'simple12'],
                ['conf1' => [1 => true], 'simple11' => [1 => false], 'simple12' => [1 => false]],
                ['simple11' => [1 => true], 'simple12' => [1 => true]],
            ],
        ];
    }
}
