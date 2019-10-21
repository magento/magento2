<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

use Magento\Catalog\Model\Indexer\Category\Product;
use Magento\Catalog\Model\Indexer\Category\Product\Plugin\StoreGroup;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Group as GroupModel;
use Magento\Store\Model\ResourceModel\Group;

class StoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var GroupModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $groupMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IndexerInterface
     */
    private $indexerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Group
     */
    private $subject;

    /**
     * @var IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $indexerRegistryMock;

    /**
     * @var StoreGroup
     */
    private $model;

    protected function setUp()
    {
        $this->groupMock = $this->createPartialMock(
            GroupModel::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup']
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->subject = $this->createMock(Group::class);
        $this->indexerRegistryMock = $this->createPartialMock(IndexerRegistry::class, ['get']);

        $this->model = (new ObjectManager($this))
            ->getObject(StoreGroup::class, ['indexerRegistry' => $this->indexerRegistryMock]);
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testBeforeAndAfterSave($valueMap)
    {
        $this->mockIndexerMethods();
        $this->groupMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(false);

        $this->model->beforeSave($this->subject, $this->groupMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->groupMock));
    }

    /**
     * @param array $valueMap
     * @dataProvider changedDataProvider
     */
    public function testBeforeAndAfterSaveNotNew($valueMap)
    {
        $this->groupMock->expects($this->exactly(2))->method('dataHasChangedFor')->willReturnMap($valueMap);
        $this->groupMock->expects($this->once())->method('isObjectNew')->willReturn(true);

        $this->model->beforeSave($this->subject, $this->groupMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->groupMock));
    }

    /**
     * @return array
     */
    public function changedDataProvider()
    {
        return [
            [
                [['root_category_id', true], ['website_id', false]],
                [['root_category_id', false], ['website_id', true]],
            ]
        ];
    }

    public function testBeforeAndAfterSaveWithoutChanges()
    {
        $this->groupMock->expects($this->exactly(2))
            ->method('dataHasChangedFor')
            ->willReturnMap([['root_category_id', false], ['website_id', false]]);
        $this->groupMock->expects($this->never())->method('isObjectNew');

        $this->model->beforeSave($this->subject, $this->groupMock);
        $this->assertSame($this->subject, $this->model->afterSave($this->subject, $this->subject, $this->groupMock));
    }

    private function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(Product::INDEXER_ID)
            ->willReturn($this->indexerMock);
    }
}
