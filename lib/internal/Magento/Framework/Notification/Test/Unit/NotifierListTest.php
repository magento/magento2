<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Notification\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class NotifierListTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testAsArraySuccess()
    {
        $notifier1 = $this->objectManagerHelper->getObject(\Magento\Framework\Notification\NotifierPool::class);
        $notifier2 = $this->objectManagerHelper->getObject(\Magento\Framework\Notification\NotifierPool::class);
        $notifierList = $this->objectManagerHelper->getObject(
            \Magento\Framework\Notification\NotifierList::class,
            [
                'objectManager' => $this->objectManager,
                'notifiers' => [$notifier1, $notifier2]
            ]
        );
        $this->setExpectedException('InvalidArgumentException');
        $result = $notifierList->asArray();
        foreach ($result as $notifier) {
            $this->assertInstanceOf(\Magento\Framework\Notification\NotifierInterface::class, $notifier);
        }
    }

    public function testAsArrayException()
    {
        $notifierCorrect = $this->objectManagerHelper->getObject(\Magento\Framework\Notification\NotifierPool::class);
        $notifierIncorrect = $this->objectManagerHelper->getObject(\Magento\Framework\Notification\NotifierList::class);
        $notifierList = $this->objectManagerHelper->getObject(
            \Magento\Framework\Notification\NotifierList::class,
            [
                'objectManager' => $this->objectManager,
                'notifiers' => [$notifierCorrect, $notifierIncorrect]
            ]
        );
        $this->setExpectedException('InvalidArgumentException');
        $notifierList->asArray();
    }
}
