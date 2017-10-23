<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Test\Unit\Model\ResourceModel;

use Magento\SalesSequence\Model\ResourceModel\Profile;

/**
 * Class ProfileTest
 */
class ProfileTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\SalesSequence\Model\ProfileFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profileFactory;

    /**
     * @var \Magento\SalesSequence\Model\Meta | \PHPUnit_Framework_MockObject_MockObject
     */
    private $meta;

    /**
     * @var \Magento\SalesSequence\Model\Profile | \PHPUnit_Framework_MockObject_MockObject
     */
    private $profile;

    /**
     * @var Profile
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
        $this->dbContext = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $this->profileFactory = $this->createPartialMock(
            \Magento\SalesSequence\Model\ProfileFactory::class,
            ['create']
        );
        $this->resourceMock = $this->createPartialMock(
            \Magento\Framework\App\ResourceConnection::class,
            ['getConnection', 'getTableName']
        );
        $this->dbContext->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->meta = $this->createMock(\Magento\SalesSequence\Model\Meta::class);
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
