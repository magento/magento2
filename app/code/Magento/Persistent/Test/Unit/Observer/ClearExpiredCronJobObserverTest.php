<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class ClearExpiredCronJobObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\ClearExpiredCronJobObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scheduleMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    protected function setUp()
    {
        $this->collectionFactoryMock =
            $this->createPartialMock(\Magento\Store\Model\ResourceModel\Website\CollectionFactory::class, ['create']);
        $this->sessionFactoryMock = $this->createPartialMock(
            \Magento\Persistent\Model\SessionFactory::class,
            ['create']
        );
        $this->scheduleMock = $this->createMock(\Magento\Cron\Model\Schedule::class);
        $this->sessionMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->websiteCollectionMock
            = $this->createMock(\Magento\Store\Model\ResourceModel\Website\Collection::class);

        $this->model = new \Magento\Persistent\Observer\ClearExpiredCronJobObserver(
            $this->collectionFactoryMock,
            $this->sessionFactoryMock
        );
    }

    public function testExecute()
    {
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->websiteCollectionMock));
        $this->websiteCollectionMock->expects($this->once())->method('getAllIds')->will($this->returnValue([1]));
        $this->sessionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->sessionMock));
        $this->sessionMock->expects($this->once())->method('deleteExpired')->with(1);
        $this->model->execute($this->scheduleMock);
    }

    public function testExecuteForNotExistingWebsite()
    {
        $this->collectionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->websiteCollectionMock));
        $this->websiteCollectionMock->expects($this->once())->method('getAllIds');
        $this->sessionFactoryMock
            ->expects($this->never())
            ->method('create');
        $this->model->execute($this->scheduleMock);
    }
}
