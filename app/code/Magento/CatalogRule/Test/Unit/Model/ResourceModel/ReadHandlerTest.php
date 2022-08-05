<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel;

use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\ResourceModel\ReadHandler;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReadHandlerTest extends TestCase
{
    /**
     * @var ReadHandler
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
        $this->subject = new ReadHandler(
            $this->resourceMock,
            $this->metadataMock
        );
    }

    public function testExecute()
    {
        $linkedField = 'entity_id';
        $entityId = 100;
        $entityType = RuleInterface::class;
        $entityData = [
            $linkedField => $entityId
        ];

        $customerGroupIds = [1, 2, 3];
        $websiteIds = [4, 5, 6];

        $metadataMock = $this->createPartialMock(
            EntityMetadata::class,
            ['getLinkField']
        );
        $this->metadataMock->expects($this->once())
            ->method('getMetadata')
            ->with($entityType)
            ->willReturn($metadataMock);

        $metadataMock->expects($this->once())->method('getLinkField')->willReturn($linkedField);

        $this->resourceMock->expects($this->once())
            ->method('getCustomerGroupIds')
            ->with($entityId)
            ->willReturn($customerGroupIds);
        $this->resourceMock->expects($this->once())
            ->method('getWebsiteIds')
            ->with($entityId)
            ->willReturn($websiteIds);

        $expectedResult = [
            $linkedField => $entityId,
            'customer_group_ids' => $customerGroupIds,
            'website_ids' => $websiteIds
        ];

        $this->assertEquals($expectedResult, $this->subject->execute($entityType, $entityData));
    }
}
