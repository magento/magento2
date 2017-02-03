<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Bundle\Test\Unit\Model\Plugin;

class BundleLoadOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Plugin\BundleLoadOptions
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionFactory;

    protected function setUp()
    {
        $this->optionListMock = $this->getMock('\Magento\Bundle\Model\Product\OptionList', [], [], '', false);
        $this->productExtensionFactory = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtensionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new \Magento\Bundle\Model\Plugin\BundleLoadOptions(
            $this->optionListMock,
            $this->productExtensionFactory
        );
    }

    public function testAroundLoadIfProductTypeNotBundle()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId'], [], '', false);
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }

    public function testAroundLoad()
    {
        $productMock = $this->getMock(
            '\Magento\Catalog\Model\Product',
            ['getTypeId', 'setExtensionAttributes'],
            [],
            '',
            false
        );
        $closure = function () use ($productMock) {
            return $productMock;
        };
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        $optionMock = $this->getMock('\Magento\Bundle\Api\Data\OptionInterface');
        $this->optionListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock)
            ->willReturn([$optionMock]);
        $productExtensionMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->disableOriginalConstructor()
            ->setMethods(['setBundleProductOptions', 'getBundleProductOptions'])
            ->getMock();
        $this->productExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($productExtensionMock);
        $productExtensionMock->expects($this->once())
            ->method('setBundleProductOptions')
            ->with([$optionMock])
            ->willReturnSelf();
        $productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($productExtensionMock)
            ->willReturnSelf();

        $this->assertEquals(
            $productMock,
            $this->model->aroundLoad($productMock, $closure, 100, null)
        );
    }
}
