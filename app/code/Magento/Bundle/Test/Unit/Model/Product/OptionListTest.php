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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Catalog\Test\Unit\Helper\OptionTestHelper;
use Magento\Bundle\Test\Unit\Helper\OptionTestHelper as BundleOptionTestHelper;

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

        $optionMock = new OptionTestHelper();
        $optionsCollMock = $this->createMock(Collection::class);
        $optionsCollMock->method('getIterator')->willReturn(new \ArrayIterator([$optionMock]));
        $this->typeMock->expects($this->once())
            ->method('getOptionsCollection')
            ->with($productMock)
            ->willReturn($optionsCollMock);

        $optionMock->setOptionId($optionId);
        $optionMock->setTestData('getData_return', $optionData);
        $optionMock->setTitle(null);
        $optionMock->setDefaultTitle($optionData['title']);

        $linkMock = $this->createMock(LinkInterface::class);
        $this->linkListMock->expects($this->once())
            ->method('getItems')
            ->with($productMock, $optionId)
            ->willReturn([$linkMock]);
        $newOptionMock = new BundleOptionTestHelper();
        $this->dataObjectHelperMock->expects($this->once())
            ->method('populateWithArray')
            ->with($newOptionMock, $optionData, OptionInterface::class)
            ->willReturnSelf();
        $newOptionMock->setOptionId($optionId);
        $newOptionMock->setTitle($optionData['title']);
        $newOptionMock->setDefaultTitle($optionData['title']);
        $newOptionMock->setSku($productSku);
        $newOptionMock->setProductLinks([$linkMock]);
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($newOptionMock);

        $this->assertEquals(
            [$newOptionMock],
            $this->model->getItems($productMock)
        );
    }
}
