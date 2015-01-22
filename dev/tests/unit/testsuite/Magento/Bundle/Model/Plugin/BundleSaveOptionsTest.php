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
        $productSku = "bundle_sku";
        $option = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $bundleProductOptionsAttrValue = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $bundleProductOptionsAttrValue->expects($this->atLeastOnce())->method('getValue')->willReturn([$option]);
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->willReturn($bundleProductOptionsAttrValue);

        $this->productOptionRepositoryMock->expects($this->once())->method('save')->with($this->productMock, $option);

        $this->productMock->expects($this->once())->method('getSku')
            ->will($this->returnValue($productSku));

        $this->productOptionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->will($this->returnValue([]));

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    /**
     * Test the case where the product has existing options
     */
    public function testAroundSaveWhenProductIsBundleWithOptionsAndExistingOptions()
    {
        $existOption1Id = 10;
        $existOption2Id = 11;
        $productSku = 'bundle_sku';
        $existingOption1 = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $existingOption1->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue($existOption1Id));
        $existingOption2 = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $existingOption2->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue($existOption2Id));

        $bundleOptionExisting = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $bundleOptionExisting->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue($existOption1Id));

        $bundleOptionNew = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $bundleOptionNew->expects($this->once())
            ->method('getOptionId')
            ->will($this->returnValue(null));

        $bundleProductOptionsAttrValue = $this->getMock('\Magento\Framework\Api\AttributeValue', [], [], '', false);
        $bundleProductOptionsAttrValue->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn([$bundleOptionExisting, $bundleOptionNew]);

        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getCustomAttribute')
            ->with('bundle_product_options')
            ->willReturn($bundleProductOptionsAttrValue);
        $this->productMock->expects($this->once())->method('getSku')
            ->will($this->returnValue($productSku));

        $this->productOptionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->will($this->returnValue([$existingOption1, $existingOption2]));

        $this->productOptionRepositoryMock
            ->expects($this->at(1))
            ->method('save')
            ->with($this->productMock, $bundleOptionExisting);

        $this->productOptionRepositoryMock
            ->expects($this->at(1))
            ->method('save')
            ->with($this->productMock, $bundleOptionNew);

        $this->productOptionRepositoryMock
            ->expects($this->once())
            ->method('delete')
            ->with($existingOption2);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
