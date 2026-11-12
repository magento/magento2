<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleConfigurable\Test\Unit\Plugin\CatalogRule\Model\Rule;

use PHPUnit\Framework\Attributes\DataProvider;
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
     */
    #[DataProvider('aroundGetMatchingProductIdsDataProvider')]
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

    #[DataProvider('ruleContextDataProvider')]
    public function testAntecedentRulesOnlyIdentifyInheritedMatches(
        array $firstMatches,
        array $secondMatches,
        bool $sameRule,
        array $expectedMatches
    ): void {
        $this->configurableProductsProviderMock->expects($this->exactly(2))->method('getIds')
            ->willReturnCallback(static fn (array $ids): array => array_intersect($ids, [100]));
        $this->configurableMock->expects($this->once())->method('getChildrenIds')
            ->with(100)->willReturn([0 => [101]]);
        $firstRule = $this->ruleMock;
        $firstRule->expects($this->once())->method('getId')->willReturn(11);
        $secondRule = $this->createStub(Rule::class);
        $secondRule->method('getId')->willReturn($sameRule ? 11 : 12);

        $this->configurableProductHandler->aroundGetMatchingProductIds(
            $firstRule,
            static fn (): array => $firstMatches
        );

        self::assertSame(
            $expectedMatches,
            $this->configurableProductHandler->aroundGetMatchingProductIds(
                $secondRule,
                static fn (): array => $secondMatches
            )
        );
    }

    public static function ruleContextDataProvider(): array
    {
        return [
            'direct child matches must not become antecedent rules' => [
                [100 => [1 => true], 101 => [1 => true]],
                [101 => [1 => true]],
                false,
                [101 => [1 => true]],
            ],
            'parent-only inheritance remains replaceable by a child rule' => [
                [100 => [1 => true]],
                [101 => [1 => true]],
                false,
                [101 => [
                    1 => true,
                    'has_antecedent_rule' => true,
                    'antecedent_rule_ids' => [1 => [11]],
                ]],
            ],
            'inheritance is scoped to the website that did not match directly' => [
                [100 => [1 => true, 2 => true], 101 => [1 => true, 2 => false]],
                [101 => [1 => true, 2 => true]],
                false,
                [101 => [
                    1 => true,
                    2 => true,
                    'has_antecedent_rule' => true,
                    'antecedent_rule_ids' => [2 => [11]],
                ]],
            ],
            'reprocessing the same rule does not inherit its previous state' => [
                [100 => [1 => true]],
                [101 => [1 => true]],
                true,
                [101 => [1 => true]],
            ],
        ];
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
