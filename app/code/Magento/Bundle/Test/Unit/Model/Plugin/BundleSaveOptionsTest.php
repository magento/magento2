<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Model\Plugin;

use \Magento\Bundle\Model\Plugin\BundleSaveOptions;

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
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productBundleOptionsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productInterfaceFactoryMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productOptionRepositoryMock = $this->getMock('Magento\Bundle\Api\ProductOptionRepositoryInterface');
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getExtensionAttributes', 'getTypeId', 'getSku', 'getStoreId'],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return $this->productMock;
        };
        $this->plugin = new BundleSaveOptions(
            $this->productOptionRepositoryMock
        );
        $this->productExtensionMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductExtension',
            ['getBundleProductOptions', 'setBundleProductOptions'],
            [],
            '',
            false
        );
        $this->productBundleOptionsMock = $this->getMock(
            'Magento\Bundle\Api\Data\OptionInterface',
            [],
            [],
            '',
            false
        );
    }

    public function testAroundSaveWhenProductIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getExtensionAttributes');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductIsBundleWithoutOptions()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
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
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([$option]);

        $this->productOptionRepositoryMock->expects($this->once())->method('save')->with($this->productMock, $option);

        $this->productMock->expects($this->exactly(2))->method('getSku')
            ->will($this->returnValue($productSku));

        $this->productOptionRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($productSku)
            ->will($this->returnValue([]));

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
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

        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([$bundleOptionExisting, $bundleOptionNew]);
        $this->productMock->expects($this->exactly(2))->method('getSku')
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

        $newProductMock = $this->getMockBuilder('Magento\Catalog\Api\Data\ProductInterface')
            ->disableOriginalConstructor()->getMock();
        $this->productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productSku, false, null, true)
            ->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
