<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\Sequence;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Meta;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMeta;
use Magento\SalesSequence\Model\ResourceModel\Profile as ResourceProfile;
use Magento\SalesSequence\Model\Sequence\DeleteByStore;
use Magento\Store\Api\Data\StoreInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DeleteByStoreTest
 */
class DeleteByStoreTest extends TestCase
{
    /**
     * @var DeleteByStore
     */
    private $deleteByStore;

    /**
     * @var ResourceMeta | MockObject
     */
    private $resourceSequenceMeta;

    /**
     * @var ResourceProfile | MockObject
     */
    private $resourceSequenceProfile;

    /**
     * @var Meta | MockObject
     */
    private $meta;

    /**
     * @var MetaFactory | MockObject
     */
    private $metaFactory;

    /**
     * @var AdapterInterface | MockObject
     */
    private $connectionMock;

    /**
     * @var ResourceConnection | MockObject
     */
    private $resourceMock;

    /**
     * @var StoreInterface | MockObject
     */
    private $store;

    protected function setUp()
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['delete']
        );
        $this->resourceSequenceMeta = $this->createPartialMock(
            ResourceMeta::class,
            ['getIdsByStore', 'load', 'delete']
        );
        $this->resourceSequenceProfile = $this->createPartialMock(
            ResourceProfile::class,
            ['getProfileIdsByMetadataIds']
        );
        $this->meta = $this->createPartialMock(
            Meta::class,
            ['getSequenceTable']
        );
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->metaFactory = $this->createPartialMock(MetaFactory::class, ['create']);
        $this->metaFactory->expects($this->any())->method('create')->willReturn($this->meta);
        $this->store = $this->getMockForAbstractClass(
            StoreInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId']
        );

        $helper = new ObjectManager($this);
        $this->deleteByStore = $helper->getObject(
            DeleteByStore::class,
            [
                'resourceMetadata' => $this->resourceSequenceMeta,
                'resourceProfile' => $this->resourceSequenceProfile,
                'metaFactory' => $this->metaFactory,
                'appResource' => $this->resourceMock,
            ]
        );
    }

    public function testExecute()
    {
        $storeId = 1;
        $metadataIds = [1, 2];
        $profileIds = [10, 11];
        $tableName = 'sales_sequence_profile';
        $this->store->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->resourceSequenceMeta->expects($this->once())
            ->method('getIdsByStore')
            ->with($storeId)
            ->willReturn($metadataIds);
        $this->resourceSequenceProfile->expects($this->once())
            ->method('getProfileIdsByMetadataIds')
            ->with($metadataIds)
            ->willReturn($profileIds);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->with($tableName)
            ->willReturn($tableName);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with($tableName, ['profile_id IN (?)' => $profileIds])
            ->willReturn(2);
        $this->resourceSequenceMeta->expects($this->any())
            ->method('load')
            ->willReturn($this->meta);
        $this->connectionMock->expects($this->any())
            ->method('dropTable')
            ->willReturn(true);
        $this->resourceSequenceMeta->expects($this->any())
            ->method('delete')
            ->willReturn($this->resourceSequenceMeta);
        $this->deleteByStore->execute($this->store);
    }
}
