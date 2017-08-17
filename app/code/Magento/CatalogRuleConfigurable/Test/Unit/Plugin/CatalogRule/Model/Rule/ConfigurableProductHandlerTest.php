<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRuleConfigurable\Test\Unit\Plugin\CatalogRule\Model\Rule;

use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider;
use Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

/**
 * Unit test for Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler
 */
class ConfigurableProductHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\Rule\ConfigurableProductHandler
     */
    private $configurableProductHandler;

    /**
     * @var Configurable|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableMock;

    /**
     * @var ConfigurableProductsProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configurableProductsProviderMock;

    /** @var \Magento\CatalogRule\Model\Rule||\PHPUnit_Framework_MockObject_MockObject */
    private $ruleMock;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->configurableMock = $this->createPartialMock(
            \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class,
            ['getChildrenIds']
        );
        $this->configurableProductsProviderMock = $this->createPartialMock(
            \Magento\CatalogRuleConfigurable\Plugin\CatalogRule\Model\ConfigurableProductsProvider::class,
            ['getIds']
        );
        $this->ruleMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);

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
