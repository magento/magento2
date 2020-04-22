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
use Magento\SalesSequence\Model\Meta;
use Magento\SalesSequence\Model\ProfileFactory;
use Magento\SalesSequence\Model\ResourceModel\Profile;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
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
     * @var ProfileFactory|MockObject
     */
    private $profileFactory;

    /**
     * @var Meta|MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Profile|MockObject
     */
    private $profile;

    /**
     * @var Profile
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
     * Initialization
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
        $this->profileFactory = $this->createPartialMock(
            ProfileFactory::class,
            ['create']
        );
        $this->resourceMock = $this->createPartialMock(
            ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->createMock(Select::class);
        $this->meta = $this->createMock(Meta::class);
        $this->profile = $this->createMock(\Magento\SalesSequence\Model\Profile::class);
        $this->resource = new Profile(
            $this->dbContext,
            $this->profileFactory
        );
    }

    public function testLoadActiveProfile()
    {
        $profileTableName = 'sequence_profile';
        $profileIdFieldName = 'profile_id';
        $metaId = 1;
        $profileId = 20;
        $profileData = [
            'profile_id' => 20,
            'meta_id' => 1
        ];
        $this->profileFactory->expects($this->once())->method('create')->willReturn($this->profile);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->resourceMock->expects($this->once())
            ->method('getTableName')
            ->willReturn($profileTableName);
        $this->connectionMock->expects($this->any())->method('select')->willReturn($this->select);
        $this->select->expects($this->at(0))
            ->method('from')
            ->with($profileTableName, [$profileIdFieldName])
            ->willReturn($this->select);
        $this->select->expects($this->at(1))
            ->method('where')
            ->with('meta_id = :meta_id')
            ->willReturn($this->select);
        $this->select->expects($this->at(2))
            ->method('where')
            ->with('is_active = 1')
            ->willReturn($this->select);
        $this->connectionMock->expects($this->once())
            ->method('fetchOne')
            ->with($this->select, ['meta_id' => $metaId])
            ->willReturn($profileId);
        $this->select->expects($this->at(3))
            ->method('from')
            ->with($profileTableName, '*', null)
            ->willReturn($this->select);
        $this->connectionMock->expects($this->any())
            ->method('quoteIdentifier');
        $this->connectionMock->expects($this->once())->method('fetchRow')->willReturn($profileData);
        $this->profile->expects($this->at(1))->method('setData')->with($profileData);
        $this->assertEquals($this->profile, $this->resource->loadActiveProfile($metaId));
    }
}
