<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Notification\Test\Unit;

use Magento\Framework\Notification\NotifierInterface;
use Magento\Framework\Notification\NotifierList;
use Magento\Framework\Notification\NotifierPool;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifierListTest extends TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var ObjectManagerInterface|MockObject */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testAsArraySuccess()
    {
        $notifier1 = $this->objectManagerHelper->getObject(NotifierPool::class);
        $notifier2 = $this->objectManagerHelper->getObject(NotifierPool::class);
        $notifierList = $this->objectManagerHelper->getObject(
            NotifierList::class,
            [
                'objectManager' => $this->objectManager,
                'notifiers' => [$notifier1, $notifier2]
            ]
        );
        $this->expectException('InvalidArgumentException');
        $result = $notifierList->asArray();
        $this->assertContainsOnlyInstancesOf(NotifierInterface::class, $result);
    }

    public function testAsArrayException()
    {
        $notifierCorrect = $this->objectManagerHelper->getObject(NotifierPool::class);
        $notifierIncorrect = $this->objectManagerHelper->getObject(NotifierList::class);
        $notifierList = $this->objectManagerHelper->getObject(
            NotifierList::class,
            [
                'objectManager' => $this->objectManager,
                'notifiers' => [$notifierCorrect, $notifierIncorrect]
            ]
        );
        $this->expectException('InvalidArgumentException');
        $notifierList->asArray();
    }
}
