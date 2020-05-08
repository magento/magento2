<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel;

use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\SaveHandler;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    protected $subject;

    /**
     * @var MockObject
     */
    protected $resourceMock;

    /**
     * @var MockObject
     */
    protected $metadataMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(Rule::class);
        $this->metadataMock = $this->createMock(MetadataPool::class);
        $this->subject = new SaveHandler(
            $this->resourceMock,
            $this->metadataMock
        );
    }

    public function testExecute()
    {
        $linkedField = 'entity_id';
        $entityId = 100;
        $entityType = RuleInterface::class;

        $customerGroupIds = '1, 2, 3';
        $websiteIds = '4, 5, 6';
        $entityData = [
            $linkedField => $entityId,
            'website_ids' => $websiteIds,
            'customer_group_ids' => $customerGroupIds
        ];

        $metadataMock = $this->createPartialMock(
            EntityMetadata::class,
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
