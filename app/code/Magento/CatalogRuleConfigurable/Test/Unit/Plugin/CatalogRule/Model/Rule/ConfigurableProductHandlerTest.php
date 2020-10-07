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
            ['getChildrenIds']
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
    public function testAfterGetMatchingProductIdsWithSimpleProduct()
    {
        $this->configurableProductsProviderMock->expects($this->once())->method('getIds')->willReturn([]);
        $this->configurableMock->expects($this->never())->method('getChildrenIds');

        $productIds = ['product' => 'valid results'];
        $this->assertEquals(
            $productIds,
            $this->configurableProductHandler->afterGetMatchingProductIds($this->ruleMock, $productIds)
        );
    }

    /**
     * @return void
     */
    public function testAfterGetMatchingProductIdsWithConfigurableProduct()
    {
        $this->configurableProductsProviderMock->expects($this->once())->method('getIds')
            ->willReturn(['conf1', 'conf2']);
        $this->configurableMock->expects($this->any())->method('getChildrenIds')->willReturnMap([
            ['conf1', true, [ 0 => ['simple1']]],
            ['conf2', true, [ 0 => ['simple1', 'simple2']]],
        ]);

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
            $this->configurableProductHandler->afterGetMatchingProductIds(
                $this->ruleMock,
                [
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
                ]
            )
        );
    }
}
