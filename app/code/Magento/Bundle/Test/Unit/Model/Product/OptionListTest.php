<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionListTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\OptionList
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $linkListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $extensionAttributesFactoryMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->typeMock = $this->createMock(\Magento\Bundle\Model\Product\Type::class);
        $this->optionFactoryMock = $this->createPartialMock(
            \Magento\Bundle\Api\Data\OptionInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->linkListMock = $this->createMock(\Magento\Bundle\Model\Product\LinksList::class);
        $this->extensionAttributesFactoryMock = $this->createMock(
            \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface::class
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

        $productMock = $this->createMock(\Magento\Catalog\Api\Data\ProductInterface::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->createPartialMock(
            \Magento\Bundle\Model\Option::class,
            ['getOptionId', 'getData', 'getTitle', 'getDefaultTitle']
        );
        $optionsCollMock = $this->getMockBuilder(\Magento\Bundle\Model\ResourceModel\Option\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionsCollMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$optionMock]));
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionsCollMock);

        $optionMock->expects($this->exactly(2))->method('getOptionId')->willReturn($optionId);
        $optionMock->expects($this->once())->method('getData')->willReturn($optionData);
        $optionMock->expects($this->once())->method('getTitle')->willReturn(null);
        $optionMock->expects($this->exactly(2))->method('getDefaultTitle')->willReturn($optionData['title']);

        $linkMock = $this->createMock(\Magento\Bundle\Api\Data\LinkInterface::class);
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
