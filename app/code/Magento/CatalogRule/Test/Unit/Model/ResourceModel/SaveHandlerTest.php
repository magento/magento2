<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel;

class SaveHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\SaveHandler
     */
    protected $subject;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(\Magento\CatalogRule\Model\ResourceModel\Rule::class);
        $this->metadataMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $this->subject = new \Magento\CatalogRule\Model\ResourceModel\SaveHandler(
            $this->resourceMock,
            $this->metadataMock
        );
    }

    public function testExecute()
    {
        $linkedField = 'entity_id';
        $entityId = 100;
        $entityType = \Magento\CatalogRule\Api\Data\RuleInterface::class;

        $customerGroupIds = '1, 2, 3';
        $websiteIds = '4, 5, 6';
        $entityData = [
            $linkedField => $entityId,
            'website_ids' => $websiteIds,
            'customer_group_ids' => $customerGroupIds
        ];

        $metadataMock = $this->createPartialMock(
            \Magento\Framework\EntityManager\EntityMetadata::class,
            ['getLinkField']
        );
        $this->metadataMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn($linkedField);

        $this->resourceMock->expects($this->at(0))
            ->method('bindRuleToEntity')
            ->with($entityId, explode(',', (string)$websiteIds), 'website')
            ->willReturnSelf();

        $this->resourceMock->expects($this->at(1))
            ->method('bindRuleToEntity')
            ->with($entityId, explode(',', (string)$customerGroupIds), 'customer_group')
            ->willReturnSelf();

        $this->assertEquals($entityData, $this->subject->execute($entityType, $entityData));
    }
}
