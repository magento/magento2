<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreGroup;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\Group as GroupModel;
use Magento\Catalog\Model\Indexer\Category\Product;

class StoreGroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Group
     */
    protected $subject;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var StoreGroup
     */
    protected $model;

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
        $this->subject = $this->getMock(Group::class, [], [], '', false);
        $this->indexerRegistryMock = $this->getMock(
            IndexerRegistry::class,
            ['get'],
            [],
            '',
            false
        );

        $this->model = new StoreGroup($this->indexerRegistryMock);
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAroundSave($valueMap)
    {
        $this->mockIndexerMethods();
        $groupMock = $this->getMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $groupMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $groupMock->expects($this->once())->method('isObjectNew')->willReturn(false);

        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $groupMock));
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testAroundSaveNotNew($valueMap)
    {
        $groupMock = $this->getMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup'],
            [],
            '',
            false
        );
        $groupMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $groupMock->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $groupMock));
    }

    public function changedDataProvider()
    {
        return [
            [
                [['root_category_id', true], ['website_id', false]],
                [['root_category_id', false], ['website_id', true]],
            ]
        ];
    }

    public function testAroundSaveWithoutChanges()
    {
        $this->groupMock = $this->getMock(
            GroupModel::class,
            [ 'dataHasChangedFor', 'isObjectNew', '__wakeup' ],
            [ ],
            '',
            false
        );
        $this->groupMock->expects($this->exactly(2))
                        ->method('dataHasChangedFor')
                        ->willReturnMap([['root_category_id', false], ['website_id', false]]);
        $this->groupMock->expects($this->never())->method('isObjectNew');

        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->groupMock ));
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
