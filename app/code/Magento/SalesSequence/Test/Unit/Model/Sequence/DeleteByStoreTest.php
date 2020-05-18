<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Test\Unit\Model\Sequence;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesSequence\Model\Meta;
use Magento\SalesSequence\Model\MetaFactory;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMeta;
use Magento\SalesSequence\Model\Sequence\DeleteByStore;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\SalesSequence\Model\Sequence\DeleteByStore class.
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
     * @var Select | MockObject
     */
    private $select;

    protected function setUp(): void
    {
        $this->connectionMock = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['delete', 'query']
        );
        $this->resourceSequenceMeta = $this->createPartialMock(
            ResourceMeta::class,
            ['load', 'delete']
        );
        $this->meta = $this->getMockBuilder(Meta::class)
            ->addMethods(['getSequenceTable'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->select = $this->createMock(Select::class);
        $this->metaFactory = $this->createPartialMock(MetaFactory::class, ['create']);
        $this->metaFactory->method('create')->willReturn($this->meta);

        $helper = new ObjectManager($this);
        $this->deleteByStore = $helper->getObject(
            DeleteByStore::class,
            [
                'resourceMetadata' => $this->resourceSequenceMeta,
                'metaFactory' => $this->metaFactory,
                'appResource' => $this->resourceMock,
            ]
        );
    }

    /**
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testExecute()
    {
        $profileTableName = 'sales_sequence_profile';
        $storeId = 1;
        $metadataIds = [1, 2];
        $profileIds = [10, 11];
        $this->resourceMock->method('getTableName')
            ->willReturnCallback(
                static function ($tableName) {
                    return $tableName;
                }
            );
        $this->resourceMock->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock
            ->method('select')
            ->willReturn($this->select);

        $this->select->method('from')
            ->willReturn($this->select);
        $this->select->method('where')
            ->willReturn($this->select);

        $this->connectionMock->method('fetchCol')
            ->willReturnCallback(
                static function ($arg, $arg2) use ($metadataIds, $profileIds) {
                    if (array_key_exists('store', $arg2)) {
                        return $metadataIds;
                    }

                    return $profileIds;
                }
            );

        $this->connectionMock->expects($this->once())
            ->method('delete')
            ->with($profileTableName, ['profile_id IN (?)' => $profileIds])
            ->willReturn(2);
        $this->resourceSequenceMeta
            ->method('load')
            ->willReturn($this->meta);
        $this->connectionMock
            ->method('dropTable')
            ->willReturn(true);
        $this->resourceSequenceMeta
            ->method('delete')
            ->willReturn($this->resourceSequenceMeta);
        $this->deleteByStore->execute($storeId);
    }
}
