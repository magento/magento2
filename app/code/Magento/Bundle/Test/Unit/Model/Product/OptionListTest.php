<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Bundle\Model\Product\LinksList;
use Magento\Bundle\Model\Product\OptionList;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\Option;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Option as CatalogOption;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OptionListTest extends TestCase
{
    use MockCreationTrait;

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

    /**
     * @inheritdoc
     */
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

    /**
     * @return void
     * @throws \Exception
     */
    public function testGetItems()
    {
        $optionId = 1;
        $optionData = ['title' => 'test title'];
        $productSku = 'product_sku';

        $productMock = $this->createMock(ProductInterface::class);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);

        $optionMock = $this->createPartialMockWithReflection(
            CatalogOption::class,
            ['getOptionId', 'getData', 'getTitle', 'getDefaultTitle']
        );
        $optionsCollMock = $this->createMock(Collection::class);
        $optionsCollMock->method('getIterator')->willReturn(new \ArrayIterator([$optionMock]));
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionsCollMock);

        $optionMock->method('getOptionId')->willReturn($optionId);
        $optionMock->method('getData')->willReturn($optionData);
        $optionMock->method('getTitle')->willReturn(null);
        $optionMock->method('getDefaultTitle')->willReturn($optionData['title']);

        $linkMock = $this->createMock(LinkInterface::class);
        $this->linkListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock, $optionId)
            ->willReturn([$linkMock]);
        $newOptionMock = $this->createPartialMockWithReflection(
            Option::class,
            ['setOptionId', 'setTitle', 'setDefaultTitle', 'setSku', 'setProductLinks']
        );
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, OptionInterface::class)
            ->willReturnSelf();
        $newOptionMock->method('setOptionId')->willReturnSelf();
        $newOptionMock->method('setTitle')->willReturnSelf();
        $newOptionMock->method('setDefaultTitle')->willReturnSelf();
        $newOptionMock->method('setSku')->willReturnSelf();
        $newOptionMock->method('setProductLinks')->willReturnSelf();
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($newOptionMock);

        $this->assertEquals(
            [$newOptionMock],
            $this->model->getItems($productMock)
        );
    }
}
