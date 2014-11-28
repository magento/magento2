<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Model\Plugin;

class BundleOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BundleOptions
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $writeServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readServiceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productRepositoryMock;

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
        $this->writeServiceMock =
            $this->getMock('Magento\Bundle\Service\V1\Product\Option\WriteService', [], [], '', false);
        $this->readServiceMock =
            $this->getMock('Magento\Bundle\Service\V1\Product\Option\ReadService', [], [], '', false);
        $this->productBuilderMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductDataBuilder',
            ['populate', 'setCustomAttribute', 'create'],
            [],
            '',
            false
        );
        $this->optionBuilderMock =
            $this->getMock('Magento\Bundle\Service\V1\Data\Product\OptionBuilder', [], [], '', false);
        $this->linkBuilderMock =
            $this->getMock('Magento\Bundle\Service\V1\Data\Product\LinkBuilder', [], [], '', false);
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $this->closureMock = function () {
            return $this->productMock;
        };
        $this->plugin = new BundleOptions(
            $this->writeServiceMock,
            $this->readServiceMock,
            $this->productBuilderMock,
            $this->optionBuilderMock,
            $this->linkBuilderMock
        );
    }

    public function testAroundSaveWhenProductIsSimple()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('simple');
        $this->productMock->expects($this->never())->method('getData')->with('bundle_product_options');
        $this->assertEquals($this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock, false));
    }

    public function testAroundSaveWhenProductIsBundle()
    {
        $this->markTestSkipped('This test should be rewritten on MAGETWO-29422');
        $bundleProductOptions = [
            [
                "product_links" => [
                    [
                        "sku" => 'product_sku'
                    ]
                ]
            ]
        ];

        $this->productMock
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
        $this->productMock
            ->expects($this->once())
            ->method('getData')
            ->with('bundle_product_options')
            ->willReturn($bundleProductOptions);
        $linkMock = $this->getMock('Magento\Bundle\Service\V1\Data\Product\Link', [], [], '', false);
        $this->linkBuilderMock->expects($this->once())->method('setSku')->willReturnSelf();
        $this->linkBuilderMock->expects($this->once())->method('create')->willReturn($linkMock);
        $optionMock = $this->getMock('Magento\Bundle\Service\V1\Data\Product\Option', [], [], '', false);
        $this->optionBuilderMock->expects($this->once())->method('setProductLinks')->willReturnSelf();
        $this->optionBuilderMock->expects($this->once())->method('create')->willReturn($optionMock);
        $this->writeServiceMock
            ->expects($this->once())
            ->method('addOptionToProduct')
            ->with($this->productMock, $optionMock);
        $this->assertEquals($this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock, false));
    }

    public function testAroundGet()
    {
        $sku = 'productSku';
        $editMode = false;
        $productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $closure = function () use ($productMock) {
            return $productMock;
        };

        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $optionMock = $this->getMock('\Magento\Bundle\Service\V1\Data\Product\Option', [], [], '', false);
        $this->readServiceMock->expects($this->once())
            ->method('getListForProduct')
            ->with($productMock)
            ->willReturn([$optionMock]);

        $this->productBuilderMock->expects($this->once())->method('populate')->with($productMock)->willReturnSelf();
        $this->productBuilderMock->expects($this->once())
            ->method('setCustomAttribute')
            ->with('bundle_product_options', [$optionMock])
            ->willReturnSelf();

        $newProductMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $this->productBuilderMock->expects($this->once())->method('create')->willReturn($newProductMock);

        $this->assertEquals(
            $newProductMock,
            $this->plugin->aroundGet($productRepositoryMock, $closure, $sku, $editMode)
        );
    }

    public function testAroundGetIfProductTypeNotBundle()
    {
        $sku = 'productSku';
        $editMode = false;
        $productRepositoryMock = $this->getMock('\Magento\Catalog\Api\ProductRepositoryInterface');
        $productMock = $this->getMock('\Magento\Catalog\Api\Data\ProductInterface');
        $closure = function () use ($productMock) {
            return $productMock;
        };

        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);

        $this->assertEquals(
            $productMock,
            $this->plugin->aroundGet($productRepositoryMock, $closure, $sku, $editMode)
        );
    }
}