<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Category\Flat\Plugin\StoreGroup;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\Group as GroupModel;

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|State
     */
    protected $stateMock;

    /**
     * @var StoreGroup
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Group
     */
    protected $subjectMock;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|GroupModel
     */
    protected $groupMock;

    protected function setUp()
    {
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->stateMock = $this->getMock(State::class, ['isFlatEnabled'], [], '', false);
        $this->subjectMock = $this->getMock(Group::class, [], [], '', false);

        $this->groupMock = $this->getMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );

        $this->indexerRegistryMock = $this->getMock(
            IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );

        $this->model = (new ObjectManager($this))
            ->getObject(
                StoreGroup::class,
                ['indexerRegistry' => $this->indexerRegistryMock, 'state' => $this->stateMock]
            );
    }

    public function testBeforeAndAfterSave()
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->willReturn(true);
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->willReturn($this->indexerMock);
        $this->groupMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('root_category_id')
            ->willReturn(true);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(false);
        $this->model->beforeSave($this->subjectMock, $this->groupMock);
        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupMock)
        );
    }

    public function testBeforeAndAfterSaveNotNew()
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
        $this->groupMock->expects($this->once())
            ->method('dataHasChangedFor')
            ->with('root_category_id')
            ->willReturn(true);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(true);
        $this->model->beforeSave($this->subjectMock, $this->groupMock);
        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $this->groupMock)
        );
    }
}
