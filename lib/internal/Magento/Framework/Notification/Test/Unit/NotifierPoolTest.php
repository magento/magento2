<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Notification\Test\Unit;

use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\NotifierList;
use Magento\Framework\Notification\NotifierPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifierPoolTest extends TestCase
{
    /** @var NotifierPool */
    protected $notifierPool;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var NotifierList|MockObject */
    protected $notifierList;

    /**
     * @var NotifierPool[]|MockObject[]
     */
    protected $notifiers;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $notifier1 = $this->createMock(NotifierPool::class);
        $notifier2 = $this->createMock(NotifierPool::class);
        $this->notifiers = [$notifier1, $notifier2];
        $this->notifierList = $this->createMock(NotifierList::class);
        $this->notifierList->expects($this->any())->method('asArray')->willReturn($this->notifiers);
        $this->notifierPool = $this->objectManagerHelper->getObject(
            NotifierPool::class,
            [
                'notifierList' => $this->notifierList
            ]
        );
    }

    public function testAdd()
    {
        $severity = MessageInterface::SEVERITY_CRITICAL;
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
