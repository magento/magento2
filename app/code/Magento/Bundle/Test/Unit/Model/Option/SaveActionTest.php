<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Option;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Option\SaveAction;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Option as OptionResource;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Bundle\Api\ProductLinkManagementAddChildrenInterface;

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
     * @var ProductInterface|MockObject
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
        $this->linkManagement = $this->getMockBuilder(ProductLinkManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->type = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->optionResource = $this->getMockBuilder(OptionResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addChildren = $this->getMockBuilder(ProductLinkManagementAddChildrenInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = $this->getMockBuilder(ProductInterface::class)
            ->addMethods(['getStoreId', 'getData', 'setIsRelationsChanged'])
            ->getMockForAbstractClass();

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
        $option = $this->getMockBuilder(Option::class)
            ->onlyMethods(['getOptionId', 'setData', 'getData'])
            ->addMethods(['setStoreId', 'setParentId', 'getParentId'])
            ->disableOriginalConstructor()
            ->getMock();
        $option->expects($this->any())
            ->method('getOptionId')
            ->willReturn(1);
        $option->expects($this->any())
            ->method('getData')
            ->willReturn([]);
        $bundleOptions = [$option];

        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('getItemById')
            ->with(1)
            ->willReturn($option);
        $this->type->expects($this->once())
            ->method('getOptionsCollection')
            ->willReturn($collection);

        $metadata = $this->getMockBuilder(EntityMetadataInterface::class)
            ->getMockForAbstractClass();
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->product->expects($this->once())
            ->method('setIsRelationsChanged')
            ->with(true);

        $this->saveAction->saveBulk($this->product, $bundleOptions);
    }
}
