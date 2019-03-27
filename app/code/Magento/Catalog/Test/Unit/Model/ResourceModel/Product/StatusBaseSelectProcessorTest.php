<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\StatusBaseSelectProcessor;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusBaseSelectProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var Select|\PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * @var StatusBaseSelectProcessor
     */
    private $statusBaseSelectProcessor;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)->disableOriginalConstructor()->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->select = $this->getMockBuilder(Select::class)->disableOriginalConstructor()->getMock();

        $this->statusBaseSelectProcessor =  (new ObjectManager($this))->getObject(StatusBaseSelectProcessor::class, [
            'eavConfig' => $this->eavConfig,
            'metadataPool' => $this->metadataPool,
            'storeManager' => $this->storeManager,
        ]);
    }

    public function testProcess()
    {
        $linkField = 'link_field';
        $backendTable = 'backend_table';
        $attributeId = 2;
        $currentStoreId = 1;

        $metadata = $this->createMock(EntityMetadataInterface::class);
        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn($linkField);
        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($metadata);

        /** @var AttributeInterface|\PHPUnit_Framework_MockObject_MockObject $statusAttribute */
        $statusAttribute = $this->getMockBuilder(AttributeInterface::class)
            ->setMethods(['getBackendTable', 'getAttributeId'])
            ->getMock();
        $statusAttribute->expects($this->atLeastOnce())
            ->method('getBackendTable')
            ->willReturn($backendTable);
        $statusAttribute->expects($this->atLeastOnce())
            ->method('getAttributeId')
            ->willReturn($attributeId);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, ProductInterface::STATUS)
            ->willReturn($statusAttribute);

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($currentStoreId);

        $this->select->expects($this->at(0))
            ->method('joinLeft')
            ->with(
                ['status_global_attr' => $backendTable],
                "status_global_attr.{$linkField} = "
                . BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . ".{$linkField}"
                . " AND status_global_attr.attribute_id = {$attributeId}"
                . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(1))
            ->method('joinLeft')
            ->with(
                ['status_attr' => $backendTable],
                "status_attr.{$linkField} = " . BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . ".{$linkField}"
                . " AND status_attr.attribute_id = {$attributeId}"
                . " AND status_attr.store_id = {$currentStoreId}",
                []
            )
            ->willReturnSelf();
        $this->select->expects($this->at(2))
            ->method('where')
            ->with('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED)
            ->willReturnSelf();

        $this->assertEquals($this->select, $this->statusBaseSelectProcessor->process($this->select));
    }
}
