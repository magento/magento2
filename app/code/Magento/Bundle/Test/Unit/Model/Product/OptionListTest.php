<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\LinksList;
use Magento\Bundle\Model\Product\OptionList;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionListTest extends TestCase
{
    /**
     * @var OptionList
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $typeMock;

    /**
     * @var MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var MockObject
     */
    protected $linkListMock;

    /**
     * @var MockObject
     */
    protected $dataObjectHelperMock;

    /**
     * @var MockObject
     */
    protected $extensionAttributesFactoryMock;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->typeMock = $this->createMock(Type::class);
        $this->optionFactoryMock = $this->createPartialMock(
            OptionInterfaceFactory::class,
            ['create']
        );
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->linkListMock = $this->createMock(LinksList::class);
        $this->extensionAttributesFactoryMock = $this->createMock(
            JoinProcessorInterface::class
        );

        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            OptionList::class,
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

        $productMock = $this->getMockForAbstractClass(ProductInterface::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->getMockBuilder(Option::class)
            ->addMethods(['getDefaultTitle'])
            ->onlyMethods(['getOptionId', 'getData', 'getTitle'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionsCollMock = $this->getMockBuilder(Collection::class)
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

        $linkMock = $this->getMockForAbstractClass(LinkInterface::class);
        $this->linkListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock, $optionId)
            ->willReturn([$linkMock]);
        $newOptionMock = $this->getMockBuilder(OptionInterface::class)
            ->addMethods(['setDefaultTitle'])
            ->getMockForAbstractClass();
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, OptionInterface::class)
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
