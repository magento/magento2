<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class ClearExpiredCronJobObserverTest extends \PHPUnit_Framework_TestCase
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
            $this->getMock(
                \Magento\Store\Model\ResourceModel\Website\CollectionFactory::class,
                ['create'],
                [],
                '',
                false
            );
        $this->sessionFactoryMock = $this->getMock(
            \Magento\Persistent\Model\SessionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->scheduleMock = $this->getMock(\Magento\Cron\Model\Schedule::class, [], [], '', false);
        $this->sessionMock = $this->getMock(\Magento\Persistent\Model\Session::class, [], [], '', false);
        $this->websiteCollectionMock
            = $this->getMock(\Magento\Store\Model\ResourceModel\Website\Collection::class, [], [], '', false);

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
