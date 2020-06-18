<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Cron\Model\Schedule;
use Magento\Persistent\Model\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\Persistent\Observer\ClearExpiredCronJobObserver;
use Magento\Store\Model\ResourceModel\Website\Collection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClearExpiredCronJobObserverTest extends TestCase
{
    /**
     * @var ClearExpiredCronJobObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $sessionFactoryMock;

    /**
     * @var MockObject
     */
    protected $scheduleMock;

    /**
     * @var MockObject
     */
    protected $websiteCollectionMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock =
            $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->sessionFactoryMock = $this->createPartialMock(
            SessionFactory::class,
            ['create']
        );
        $this->scheduleMock = $this->createMock(Schedule::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->websiteCollectionMock
            = $this->createMock(Collection::class);

        $this->model = new ClearExpiredCronJobObserver(
            $this->collectionFactoryMock,
            $this->sessionFactoryMock
        );
    }

    public function testExecute()
    {
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->websiteCollectionMock);
        $this->websiteCollectionMock->expects($this->once())->method('getAllIds')->willReturn([1]);
        $this->sessionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('deleteExpired')->with(1);
        $this->model->execute($this->scheduleMock);
    }

    public function testExecuteForNotExistingWebsite()
    {
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->websiteCollectionMock);
        $this->websiteCollectionMock->expects($this->once())->method('getAllIds');
        $this->sessionFactoryMock
            ->expects($this->never())
            ->method('create');
        $this->model->execute($this->scheduleMock);
    }
}
