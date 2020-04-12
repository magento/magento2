<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Category\Flat\Plugin\StoreView;
use Magento\Catalog\Model\Indexer\Category\Flat\State;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Store\Model\ResourceModel\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreViewTest extends TestCase
{
    /**
     * @var MockObject|IndexerInterface
     */
    protected $indexerMock;

    /**
     * @var MockObject|State
     */
    protected $stateMock;

    /**
     * @var StoreView
     */
    protected $model;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
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
        $this->stateMock = $this->createPartialMock(
            State::class,
            ['isFlatEnabled']
        );
        $this->subjectMock = $this->createMock(Store::class);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->model = new StoreView($this->indexerRegistryMock, $this->stateMock);
    }

    public function testBeforeAndAfterSaveNewObject()
    {
        $this->mockConfigFlatEnabled();
        $this->mockIndexerMethods();
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['isObjectNew', 'dataHasChangedFor', '__wakeup']
        );
        $storeMock->expects($this->once())->method('isObjectNew')->will($this->returnValue(true));
        $this->model->beforeSave($this->subjectMock, $storeMock);
        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    public function testBeforeAndAfterSaveHasChanged()
    {
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['isObjectNew', 'dataHasChangedFor', '__wakeup']
        );
        $this->model->beforeSave($this->subjectMock, $storeMock);
        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    public function testBeforeAndAfterSaveNoNeed()
    {
        $this->mockConfigFlatEnabledNever();
        $storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['isObjectNew', 'dataHasChangedFor', '__wakeup']
        );
        $this->model->beforeSave($this->subjectMock, $storeMock);
        $this->assertSame(
            $this->subjectMock,
            $this->model->afterSave($this->subjectMock, $this->subjectMock, $storeMock)
        );
    }

    protected function mockIndexerMethods()
    {
        $this->indexerMock->expects($this->once())->method('invalidate');
        $this->indexerRegistryMock->expects($this->once())
            ->method('get')
            ->with(State::INDEXER_ID)
            ->will($this->returnValue($this->indexerMock));
    }

    protected function mockConfigFlatEnabled()
    {
        $this->stateMock->expects($this->once())->method('isFlatEnabled')->will($this->returnValue(true));
    }

    protected function mockConfigFlatEnabledNever()
    {
        $this->stateMock->expects($this->never())->method('isFlatEnabled');
    }
}
