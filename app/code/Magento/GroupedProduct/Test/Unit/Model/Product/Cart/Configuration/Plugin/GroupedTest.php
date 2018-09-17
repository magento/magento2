<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product\Cart\Configuration\Plugin;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin\Grouped
     */
    protected $groupedPlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Model\Product\CartConfiguration',
            [],
            [],
            '',
            false
        );
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
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );
        $this->assertEquals(
            true,
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
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('product'));
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
