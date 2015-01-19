<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Model\Observer;

class ClearExpiredCronJobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Observer\ClearExpiredCronJob
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
            $this->getMock('Magento\Store\Model\Resource\Website\CollectionFactory', ['create'], [], '', false);
        $this->sessionFactoryMock = $this->getMock(
            'Magento\Persistent\Model\SessionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->scheduleMock = $this->getMock('\Magento\Cron\Model\Schedule', [], [], '', false);
        $this->sessionMock = $this->getMock('\Magento\Persistent\Model\Session', [], [], '', false);
        $this->websiteCollectionMock
            = $this->getMock('\Magento\Store\Model\Resource\Website\Collection', [], [], '', false);

        $this->model = new \Magento\Persistent\Model\Observer\ClearExpiredCronJob(
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
