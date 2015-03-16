<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productRepositoryMock = $this->getMock('Magento\Catalog\Api\ProductRepositoryInterface');
        $this->productOptionRepositoryMock = $this->getMock('Magento\Bundle\Api\ProductOptionRepositoryInterface');
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getExtensionAttributes', 'getTypeId'],
            [],
            '',
            false
        );
        $this->closureMock = function () {
            return $this->productMock;
        };
        $this->plugin = new BundleSaveOptions($this->productOptionRepositoryMock);
        $this->productExtensionMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductExtension',
            ['getBundleProductOptions'],
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
            ->willReturn([]);

        $this->productOptionRepositoryMock->expects($this->never())->method('save');

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }

    public function testAroundSaveWhenProductIsBundleWithOptions()
    {
        $this->productMock->expects($this->once())->method('getTypeId')->willReturn('bundle');
        $this->productMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($this->productExtensionMock);
        $this->productExtensionMock->expects($this->once())
            ->method('getBundleProductOptions')
            ->willReturn([$this->productBundleOptionsMock]);

        $this->productOptionRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->productMock, $this->productBundleOptionsMock);

        $this->assertEquals(
            $this->productMock,
            $this->plugin->aroundSave($this->productRepositoryMock, $this->closureMock, $this->productMock)
        );
    }
}
