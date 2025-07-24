<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\Profile;
use Magento\SalesSequence\Model\ResourceModel\Meta;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetaTest extends TestCase
{
    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Context|MockObject
     */
    private $dbContext;

    /**
     * @var MetaFactory|MockObject
     */
    private $metaFactory;

    /**
     * @var \Magento\SalesSequence\Model\Meta|MockObject
     */
    private $meta;

    /**
     * @var Profile|MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Profile|MockObject
     */
    private $resourceProfile;

    /**
     * @var Meta
     */
    private $resource;

    /**
     * @var Resource|MockObject
     */
    protected $resourceMock;

    /**
     * @var Select|MockObject
     */
    private $select;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->dbContext = $this->createMock(Context::class);
        $this->metaFactory = $this->createPartialMock(MetaFactory::class, ['create']);
        $this->resourceProfile = $this->createPartialMock(
            \Magento\SalesSequence\Model\ResourceModel\Profile::class,
            ['loadActiveProfile', 'save']
        );
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->createMock(Select::class);
        $this->meta = $this->createMock(\Magento\SalesSequence\Model\Meta::class);
        $this->profile = $this->createMock(Profile::class);
        $this->resource = new Meta(
            $this->dbContext,
            $this->metaFactory,
            $this->resourceProfile
        );
    }

    /**
     * @return void
     */
    public function testLoadBy(): void
    {
        $metaTableName = 'sequence_meta';
        $metaIdFieldName = 'meta_id';
        $entityType = 'order';
        $storeId = 1;
        $metaId = 1;
        $metaData = [
            'meta_id' => 1,
            'profile_id' => 2
        ];
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($metaTableName);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);

        $this->select
            ->method('where')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 === 'entity_type = :entity_type AND store_id = :store_id') {
                    return $this->select;
                }
            });
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->select, ['entity_type' => $entityType, 'store_id' => $storeId])
            ->willReturn($metaId);
        $this->metaFactory->expects($this->once())->method('create')->willReturn($this->meta);

        $this->select
            ->method('from')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($metaTableName, $metaIdFieldName) {
                if ($arg1 == $metaTableName && $arg2 == [$metaIdFieldName]) {
                    return $this->select;
                } elseif ($arg1 == 'sequence_meta' && $arg2 == '*' && $arg3 === null) {
                    return $this->select;
                }
            });

        // Check Save with Active Profile
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier');
        $this->connectionMock->expects($this->once())->method('fetchRow')->willReturn($metaData);
        $this->resourceProfile->expects($this->once())->method('loadActiveProfile')->willReturn($this->profile);

        $this->meta->expects($this->once())->method('beforeLoad');
        $this->assertEquals($this->meta, $this->resource->loadByEntityTypeAndStore($entityType, $storeId));
    }
}
