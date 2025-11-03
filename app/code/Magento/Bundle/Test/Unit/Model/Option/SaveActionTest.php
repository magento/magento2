<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Option;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option as OptionResource;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Bundle\Api\ProductLinkManagementAddChildrenInterface;

/**
 * Test class for \Magento\Bundle\Model\Option\SaveAction
 */
class SaveActionTest extends TestCase
{
    /**
     * @var Option|MockObject
     */
    private $optionResource;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var Type|MockObject
     */
    private $type;

    /**
     * @var ProductLinkManagementInterface|MockObject
     */
    private $linkManagement;

    /**
     * @var ProductTestHelper
     */
    private $product;

    /**
     * @var SaveAction
     */
    private $saveAction;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductLinkManagementAddChildrenInterface
     */
    private $addChildren;

    protected function setUp(): void
    {
        $this->linkManagement = $this->createMock(ProductLinkManagementInterface::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->type = $this->createMock(Type::class);
        $this->optionResource = $this->createMock(OptionResource::class);
        $this->addChildren = $this->createMock(ProductLinkManagementAddChildrenInterface::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->product = new ProductTestHelper();

        $this->saveAction = new SaveAction(
            $this->optionResource,
            $this->metadataPool,
            $this->type,
            $this->linkManagement,
            $this->storeManager,
            $this->addChildren
        );
    }

    public function testSaveBulk()
    {
        $option = $this->createMock(Option::class);
        $option->method('getOptionId')->willReturn(1);
        $option->method('getData')->willReturn([]);
        $bundleOptions = [$option];

        $collection = $this->createMock(Collection::class);
        $collection->expects($this->once())
            ->method('getItemById')
            ->with(1)
            ->willReturn($option);
        $this->type->expects($this->once())
            ->method('getOptionsCollection')
            ->willReturn($collection);

        $metadata = $this->createMock(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        // Clean setter call - no complex expectations needed
        $this->product->setIsRelationsChanged(true);

        $this->saveAction->saveBulk($this->product, $bundleOptions);
    }
}
