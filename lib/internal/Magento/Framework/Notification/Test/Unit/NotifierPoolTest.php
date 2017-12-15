<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class NotifierPoolTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Notification\NotifierPool */
    protected $notifierPool;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\Notification\NotifierList|\PHPUnit_Framework_MockObject_MockObject */
    protected $notifierList;

    /**
     * @var \Magento\Framework\Notification\NotifierPool[]|\PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $notifiers;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $notifier1 = $this->createMock(\Magento\Framework\Notification\NotifierPool::class);
        $notifier2 = $this->createMock(\Magento\Framework\Notification\NotifierPool::class);
        $this->notifiers = [$notifier1, $notifier2];
        $this->notifierList = $this->createMock(\Magento\Framework\Notification\NotifierList::class);
        $this->notifierList->expects($this->any())->method('asArray')->will($this->returnValue($this->notifiers));
        $this->notifierPool = $this->objectManagerHelper->getObject(
            \Magento\Framework\Notification\NotifierPool::class,
            [
                'notifierList' => $this->notifierList
            ]
        );
    }

    public function testAdd()
    {
        $severity = \Magento\Framework\Notification\MessageInterface::SEVERITY_CRITICAL;
        $title = 'title';
        $description = 'desc';
        foreach ($this->notifiers as $notifier) {
            $notifier->expects($this->once())->method('add')->with($severity, $title, $description);
        }
        $this->notifierPool->add($severity, $title, $description);
    }

    public function testAddCritical()
    {
        $title = 'title';
        $description = 'desc';
        foreach ($this->notifiers as $notifier) {
            $notifier->expects($this->once())->method('addCritical')->with($title, $description);
        }
        $this->notifierPool->addCritical($title, $description);
    }

    public function testAddMajor()
    {
        $title = 'title';
        $description = 'desc';
        foreach ($this->notifiers as $notifier) {
            $notifier->expects($this->once())->method('addMajor')->with($title, $description);
        }
        $this->notifierPool->addMajor($title, $description);
    }

    public function testAddMinor()
    {
        $title = 'title';
        $description = 'desc';
        foreach ($this->notifiers as $notifier) {
            $notifier->expects($this->once())->method('addMinor')->with($title, $description);
        }
        $this->notifierPool->addMinor($title, $description);
    }

    public function testAddNotice()
    {
        $title = 'title';
        $description = 'desc';
        foreach ($this->notifiers as $notifier) {
            $notifier->expects($this->once())->method('addNotice')->with($title, $description);
        }
        $this->notifierPool->addNotice($title, $description);
    }
}
