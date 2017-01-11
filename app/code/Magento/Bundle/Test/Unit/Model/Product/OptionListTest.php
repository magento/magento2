<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\OptionList
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->typeMock = $this->getMock(\Magento\Bundle\Model\Product\Type::class, [], [], '', false);
        $this->optionFactoryMock = $this->getMock(
            \Magento\Bundle\Api\Data\OptionInterfaceFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->dataObjectHelperMock = $this->getMock(\Magento\Framework\Api\DataObjectHelper::class, [], [], '', false);
        $this->linkListMock = $this->getMock(\Magento\Bundle\Model\Product\LinksList::class, [], [], '', false);
        $this->extensionAttributesFactoryMock = $this->getMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class,
            [],
            [],
            '',
            false
        );

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            \Magento\Bundle\Model\Product\OptionList::class,
            [
                'type' => $this->typeMock,
                'optionFactory' => $this->optionFactoryMock,
                'linkList' => $this->linkListMock,
                'dataObjectHelper' => $this->dataObjectHelperMock,
                'extensionAttributesJoinProcessor' => $this->extensionAttributesFactoryMock
            ]
        );
    }

    public function testGetItems()
    {
        $optionId = 1;
        $optionData = ['title' => 'test title'];
        $productSku = 'product_sku';

        $productMock = $this->getMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMock(
            \Magento\Bundle\Model\Option::class,
            ['getOptionId', 'getData', 'getTitle', 'getDefaultTitle'],
            [],
            '',
            false
        );
        $optionsCollMock = $this->objectManager->getCollectionMock(
            \Magento\Bundle\Model\ResourceModel\Option\Collection::class,
            [$optionMock]
        );
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionsCollMock);

        $optionMock->expects($this->exactly(2))->method('getOptionId')->willReturn($optionId);
        $optionMock->expects($this->once())->method('getData')->willReturn($optionData);
        $optionMock->expects($this->once())->method('getTitle')->willReturn(null);
        $optionMock->expects($this->exactly(2))->method('getDefaultTitle')->willReturn($optionData['title']);

        $linkMock = $this->getMock(\Magento\Bundle\Api\Data\LinkInterface::class);
        $this->linkListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock, $optionId)
            ->willReturn([$linkMock]);
        $newOptionMock = $this->getMockBuilder(\Magento\Bundle\Api\Data\OptionInterface::class)
            ->setMethods(['setDefaultTitle'])
            ->getMockForAbstractClass();
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, \Magento\Bundle\Api\Data\OptionInterface::class)
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setTitle')
            ->with($optionData['title'])
            ->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setDefaultTitle')
            ->with($optionData['title'])
            ->willReturnSelf();
        $newOptionMock->expects($this->once())->method('setSku')->with($productSku)->willReturnSelf();
        $newOptionMock->expects($this->once())
            ->method('setProductLinks')
            ->with([$linkMock])
            ->willReturnSelf();
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($newOptionMock);

        $this->assertEquals(
            [$newOptionMock],
            $this->model->getItems($productMock)
        );
    }
}
