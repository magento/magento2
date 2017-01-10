<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\SalesSequence\Model\ResourceModel\Meta;

/**
 * Class MetaTest
 */
class MetaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\Context | \PHPUnit_Framework_MockObject_MockObject
     */
    private $dbContext;

    /**
     * @var \Magento\SalesSequence\Model\MetaFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $metaFactory;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    /**
     * @var \Magento\SalesSequence\Model\ResourceModel\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceProfile;

    /**
     * @var Meta
     */
    private $resource;

    /**
     * @var Resource | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\DB\Select | \PHPUnit_Framework_MockObject_MockObject
     */
    private $select;

    /**
     * Initialization
     */
    protected function setUp()
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
        $this->dbContext = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\Context::class,
            [],
            [],
            '',
            false
        );
        $this->metaFactory = $this->getMock(
            \Magento\SalesSequence\Model\MetaFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->resourceProfile = $this->getMock(
            \Magento\SalesSequence\Model\ResourceModel\Profile::class,
            ['loadActiveProfile', 'save'],
            [],
            '',
            false
        );
        $this->resourceMock = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection', 'getTableName'],
            [],
            '',
            false
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->getMock(
            \Magento\Framework\DB\Select::class,
            [],
            [],
            '',
            false
        );
        $this->meta = $this->getMock(
            \Magento\SalesSequence\Model\Meta::class,
            [],
            [],
            '',
            false
        );
        $this->profile = $this->getMock(
            \Magento\SalesSequence\Model\Profile::class,
            [],
            [],
            '',
            false
        );
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
