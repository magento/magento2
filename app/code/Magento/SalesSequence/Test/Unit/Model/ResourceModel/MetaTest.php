<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel;

use Magento\SalesSequence\Model\ResourceModel\Meta;

/**
 * Class MetaTest
 */
class MetaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context | \PHPUnit\Framework\MockObject\MockObject
     */
    private $dbContext;

    /**
     * @var \Magento\SalesSequence\Model\MetaFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    private $metaFactory;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit\Framework\MockObject\MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Profile | \PHPUnit\Framework\MockObject\MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Profile | \PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceProfile;

    /**
     * @var Meta
     */
    private $resource;

    /**
     * @var Resource | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select | \PHPUnit\Framework\MockObject\MockObject
     */
    private $select;

    /**
     * Initialization
     */
    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['query']
        );
        $this->dbContext = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->metaFactory = $this->createPartialMock(\Magento\SalesSequence\Model\MetaFactory::class, ['create']);
        $this->resourceProfile = $this->createPartialMock(
            \Magento\SalesSequence\Model\ResourceModel\Profile::class,
            ['loadActiveProfile', 'save']
        );
        $this->resourceMock = $this->createPartialMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->meta = $this->createMock(\Magento\SalesSequence\Model\Meta::class);
        $this->profile = $this->createMock(\Magento\SalesSequence\Model\Profile::class);
        $this->resource = new Meta(
            $this->dbContext,
            $this->metaFactory,
            $this->resourceProfile
        );
    }

    public function testLoadBy()
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
        $this->select->expects($this->at(0))
            ->method('from')
            ->with($metaTableName, [$metaIdFieldName])
            ->willReturn($this->select);
        $this->select->expects($this->at(1))
            ->method('where')
            ->with('entity_type = :entity_type AND store_id = :store_id')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->select, ['entity_type' => $entityType, 'store_id' => $storeId])
            ->willReturn($metaId);
        $this->metaFactory->expects($this->once())->method('create')->willReturn($this->meta);
        $this->stepCheckSaveWithActiveProfile($metaData);
        $this->meta->expects($this->once())->method('beforeLoad');
        $this->assertEquals($this->meta, $this->resource->loadByEntityTypeAndStore($entityType, $storeId));
    }

    /**
     * @param $metaData
     */
    private function stepCheckSaveWithActiveProfile($metaData)
    {
        $this->select->expects($this->at(2))
            ->method('from')
            ->with('sequence_meta', '*', null)
            ->willReturn($this->select);
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier');
        $this->connectionMock->expects($this->once())->method('fetchRow')->willReturn($metaData);
        $this->resourceProfile->expects($this->once())->method('loadActiveProfile')->willReturn($this->profile);
    }
}
