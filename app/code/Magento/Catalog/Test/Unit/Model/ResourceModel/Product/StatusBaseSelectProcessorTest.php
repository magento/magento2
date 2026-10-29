<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\StatusBaseSelectProcessor;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StatusBaseSelectProcessorTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $eavConfig;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @var StatusBaseSelectProcessor
     */
    private $statusBaseSelectProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->eavConfig = $this->createMock(Config::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->select = $this->createMock(Select::class);

        $this->statusBaseSelectProcessor =  (new ObjectManager($this))->getObject(StatusBaseSelectProcessor::class, [
            'eavConfig' => $this->eavConfig,
            'metadataPool' => $this->metadataPool,
            'storeManager' => $this->storeManager
        ]);
    }

    /**
     * @return void
     */
    public function testProcess(): void
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

        /** @var AttributeInterface $statusAttribute */
        $statusAttribute = $this->createPartialMock(DataObject::class, []);
        $statusAttribute->setData('backend_table', $backendTable);
        $statusAttribute->setData('attribute_id', $attributeId);
        $this->eavConfig->expects($this->once())
            ->method('getAttribute')
            ->with(Product::ENTITY, ProductInterface::STATUS)
            ->willReturn($statusAttribute);

        $storeMock = $this->createMock(StoreInterface::class);

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($currentStoreId);

        $this->select
            ->method('joinLeft')
            ->willReturnCallback(function (...$args) use ($backendTable, $linkField, $attributeId, $currentStoreId) {
                static $index = 0;
                $expectedArgs = [
                [
                    ['status_global_attr' => $backendTable],
                    "status_global_attr.{$linkField} = " . BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS .
                    ".{$linkField}"
                    . " AND status_global_attr.attribute_id = {$attributeId}"
                    . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
                    []
                ],
                [
                    ['status_attr' => $backendTable],
                    "status_attr.{$linkField} = " . BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . ".{$linkField}"
                    . " AND status_attr.attribute_id = {$attributeId}"
                    . " AND status_attr.store_id = {$currentStoreId}",
                    []
                ]
                ];
                $returnValue = $this->select;
                $index++;
                return $args === $expectedArgs[$index - 1] ? $returnValue : null;
            });
        $this->select
            ->method('where')
            ->with('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED)
            ->willReturnSelf();

        $this->assertEquals($this->select, $this->statusBaseSelectProcessor->process($this->select));
    }
}
