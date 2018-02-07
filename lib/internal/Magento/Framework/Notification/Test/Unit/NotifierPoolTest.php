<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class NotifierPoolTest extends \PHPUnit_Framework_TestCase
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
        $notifier1 = $this->getMock('Magento\Framework\Notification\NotifierPool', [], [], '', false);
        $notifier2 = $this->getMock('Magento\Framework\Notification\NotifierPool', [], [], '', false);
        $this->notifiers = [$notifier1, $notifier2];
        $this->notifierList = $this->getMock('Magento\Framework\Notification\NotifierList', [], [], '', false);
        $this->notifierList->expects($this->any())->method('asArray')->will($this->returnValue($this->notifiers));
        $this->notifierPool = $this->objectManagerHelper->getObject(
            'Magento\Framework\Notification\NotifierPool',
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
