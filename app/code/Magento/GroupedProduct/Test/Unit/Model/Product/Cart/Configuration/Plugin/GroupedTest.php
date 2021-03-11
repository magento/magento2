<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Cart\Configuration\Plugin;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin\Grouped
     */
    protected $groupedPlugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp(): void
    {
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\CartConfiguration::class);
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->groupedPlugin = new \Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin\Grouped();
    }

    public function testAroundIsProductConfiguredWhenProductGrouped()
    {
        $config = ['super_group' => 'product'];
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->willReturn(
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE
        );
        $this->assertTrue(
            $this->groupedPlugin->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }

    public function testAroundIsProductConfiguredWhenProductIsNotGrouped()
    {
        $config = ['super_group' => 'product'];
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('product');
        $this->assertEquals(
            'Expected',
            $this->groupedPlugin->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }
}
