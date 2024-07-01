<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\Test\Unit;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Ddl\Trigger;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mview\View\CollectionFactory;
use Magento\Framework\Mview\View\CollectionInterface;
use Magento\Framework\Mview\View\Subscription;
use Magento\Framework\Mview\View;
use Magento\Framework\Mview\ViewFactory;
use Magento\Framework\Mview\TriggerCleaner;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * test Mview TriggerCleaner functionality
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TriggerCleanerTest extends TestCase
{
    /**
     * @var View
     */
    private $model;

    /**
     * @var MockObject|CollectionFactory
     */
    private $viewCollectionFactory;

    /**
     * @var MockObject|ResourceConnection
     */
    private $resource;

    /**
     * @var MockObject|ViewFactory
     */
    private $viewFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->viewCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMockForAbstractClass();
        $this->viewFactory = $this->createMock(ViewFactory::class);
        $this->model = new TriggerCleaner(
            $this->viewCollectionFactory,
            $this->resource,
            $this->viewFactory
        );
    }

    /**
     * Test triggers aren't recreated if their action statements are unchanged
     *
     * @return void
     */
    public function testRemoveTriggersNoChanges(): void
    {
        $DBTriggers = [
            'trg_catalog_category_entity_int_after_insert' => [
                'TRIGGER_NAME' => 'trg_catalog_category_entity_int_after_insert',
                'ACTION_STATEMENT' => 'BEGIN statement; END',
                'EVENT_OBJECT_TABLE' => 'catalog_category_entity_int'
            ]
        ];

        $connectionMock = $this->getConnectionMock();
        $connectionMock->expects($this->once())
            ->method('fetchAssoc')
            ->willReturn($DBTriggers);

        $this->resource->expects($this->once())->method('getConnection')->willReturn($connectionMock);

        $triggerMock = $this->getMockBuilder(Trigger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName', 'getStatements'])
            ->getMockForAbstractClass();
        $triggerMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn('trg_catalog_category_entity_int_after_insert');
        $triggerMock->expects($this->once())->method('getStatements')->willReturn(['statement;']);

        $subscriptionMock = $this->createMock(Subscription::class);
        $subscriptionMock->expects($this->once())->method('getTriggers')->willReturn([$triggerMock]);
        $subscriptionMock->expects($this->once())->method('create')->willReturn($subscriptionMock);

        $viewMock = $this->createMock(View::class);
        $viewMock->expects($this->once())
            ->method('getSubscriptions')
            ->willReturn(['subscriptionConfig' => []]);
        $viewMock->expects($this->once())->method('initSubscriptionInstance')->willReturn($subscriptionMock);

        $viewCollectionMock = $this->getMockForAbstractClass(CollectionInterface::class);
        $viewCollectionMock->expects($this->once())->method('getViewsByStateMode')->willReturn([$viewMock]);

        $this->viewCollectionFactory->expects($this->once())->method('create')->willReturn($viewCollectionMock);

        $subscriptionMock->expects($this->never())->method('saveTrigger');
        $viewMock->expects($this->never())->method('unsubscribe');
        $this->model->removeTriggers();
    }

    /**
     * Prepare connection mock
     *
     * @return AdapterInterface|MockObject
     */
    private function getConnectionMock()
    {
        $selectMock = $this->createMock(Select::class);

        $selectMock->expects($this->once())
            ->method('from')
            ->willReturn($selectMock);

        $selectMock->expects($this->once())
            ->method('where');

        $connectionMock = $this->createMock(AdapterInterface::class);

        $connectionMock->expects($this->once())
            ->method('select')
            ->willReturn($selectMock);

        return $connectionMock;
    }
}
