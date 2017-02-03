<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin;

use Magento\ConfigurableProduct\Model\Plugin\AfterProductLoad;

class AfterProductLoadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AfterProductLoad
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionValueFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productExtensionFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Magento\Catalog\Model\Product
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableProductTypeInstanceMock;

    protected function setUp()
    {
        $this->optionValueFactory = $this->getMock(
            '\Magento\ConfigurableProduct\Api\Data\OptionValueInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableProductTypeInstanceMock = $this->getMock(
            '\Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            [],
            [],
            '',
            false
        );
        $this->productMock->expects($this->any())
            ->method('getTypeInstance')
            ->willReturn($this->configurableProductTypeInstanceMock);
        $this->productExtensionFactory = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtensionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new \Magento\ConfigurableProduct\Model\Plugin\AfterProductLoad(
            $this->productExtensionFactory,
            $this->optionValueFactory
        );
    }

    protected function setupOptions()
    {
        $optionValues = [
            [
                'value_index' => 5,
            ],
            [
                'value_index' => 6,
            ],
        ];
        $optionMock1 = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'setValues'])
            ->getMock();
        $optionMock1->expects($this->once())
            ->method('getOptions')
            ->willReturn($optionValues);

        $optionValueMock1 = $this->getMockBuilder('\Magento\ConfigurableProduct\Api\Data\OptionValueInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $optionValueMock1->expects($this->once())
            ->method('setValueIndex')
            ->with($optionValues[0]['value_index'])
            ->willReturnSelf();

        $optionValueMock2 = $this->getMockBuilder('\Magento\ConfigurableProduct\Api\Data\OptionValueInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $optionValueMock2->expects($this->once())
            ->method('setValueIndex')
            ->with($optionValues[1]['value_index'])
            ->willReturnSelf();

        $this->optionValueFactory->expects($this->at(0))
            ->method('create')
            ->willReturn($optionValueMock1);
        $optionMock1->expects($this->once())
            ->method('setValues')
            ->with([$optionValueMock1, $optionValueMock2]);

        $optionMock2 = $this->getMockBuilder('\Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute')
            ->disableOriginalConstructor()
            ->setMethods(['getOptions', 'setValues'])
            ->getMock();
        $optionMock2->expects($this->once())
            ->method('getOptions')
            ->willReturn([]);
        $optionMock2->expects($this->once())
            ->method('setValues')
            ->with([]);
        $this->optionValueFactory->expects($this->at(1))
            ->method('create')
            ->willReturn($optionValueMock2);

        $options = [$optionMock1, $optionMock2];

        $this->configurableProductTypeInstanceMock->expects($this->once())
            ->method('getConfigurableAttributes')
            ->with($this->productMock)
            ->willReturn($options);
        return $options;
    }

    protected function setupLinks()
    {
        $id = 5;
        $links = [6, 7];
        $this->productMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $this->configurableProductTypeInstanceMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($id)
            ->willReturn([$links]);
        return $links;
    }

    public function testAfterLoad()
    {
        $options = $this->setupOptions();
        $links = $this->setupLinks();

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $extensionAttributeMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMock();

        $extensionAttributeMock->expects($this->once())->method('setConfigurableProductOptions')
            ->with($options)
            ->willReturnSelf();
        $extensionAttributeMock->expects($this->once())->method('setConfigurableProductLinks')
            ->with($links)
            ->willReturnSelf();

        $this->productExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributeMock);

        $this->productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributeMock)
            ->willReturnSelf();

        $this->assertEquals($this->productMock, $this->model->afterLoad($this->productMock));
    }

    public function testAfterLoadNotConfigurableProduct()
    {
        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->productMock->expects($this->never())
            ->method('getExtensionAttributes');
        $this->productMock->expects($this->never())
            ->method('setExtensionAttributes');
        $this->assertEquals($this->productMock, $this->model->afterLoad($this->productMock));
    }

    public function testAfterLoadNoLinks()
    {
        $options = $this->setupOptions();

        $this->productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);

        $extensionAttributeMock = $this->getMockBuilder('\Magento\Catalog\Api\Data\ProductExtension')
            ->setMethods(['setConfigurableProductOptions', 'setConfigurableProductLinks'])
            ->getMock();

        $extensionAttributeMock->expects($this->once())->method('setConfigurableProductOptions')
            ->with($options)
            ->willReturnSelf();
        $extensionAttributeMock->expects($this->once())->method('setConfigurableProductLinks')
            ->with([])
            ->willReturnSelf();

        $this->productExtensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($extensionAttributeMock);

        $this->productMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extensionAttributeMock)
            ->willReturnSelf();

        $this->assertEquals($this->productMock, $this->model->afterLoad($this->productMock));
    }
}
