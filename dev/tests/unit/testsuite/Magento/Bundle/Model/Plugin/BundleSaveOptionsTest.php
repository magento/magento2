<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Plugin;

class BundleSaveOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BundleSaveOptions
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productOptionRepositoryMock = $this->getMock('Magento\Bundle\Api\ProductOptionRepositoryInterface');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->closureMock = function () {
            return $this->productMock;
        };
        $this->plugin = new BundleSaveOptions($this->productOptionRepositoryMock);
    }

    public function testAroundSaveWhenProductIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getCustomAttribute');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductIsBundleWithoutOptions()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->willReturn(null);

        $this->productOptionRepositoryMock->expects($this->never())->method('save');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductIsBundleWithOptions()
    {
        $option = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $bundleProductOptionsAttrValue = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $bundleProductOptionsAttrValue->expects($this->atLeastOnce())->method('getValue')->willReturn([$option]);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->willReturn($bundleProductOptionsAttrValue);

        $this->productOptionRepositoryMock->expects($this->once())->method('save')->with($this->productMock, $option);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
