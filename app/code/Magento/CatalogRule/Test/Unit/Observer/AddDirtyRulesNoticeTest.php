<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Observer;

use Magento\CatalogRule\Model\Flag;
use Magento\CatalogRule\Observer\AddDirtyRulesNotice;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddDirtyRulesNoticeTest extends TestCase
{
    /**
     * @var AddDirtyRulesNotice
     */
    private $observer;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManagerMock;

    protected function setUp(): void
    {
        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerHelper = new ObjectManager($this);
        $this->observer = $objectManagerHelper->getObject(
            AddDirtyRulesNotice::class,
            [
                'messageManager' => $this->messageManagerMock,
            ]
        );
    }

    public function testExecute()
    {
        $message = "test";
        $flagMock = $this->getMockBuilder(Flag::class)
            ->setMethods(['getState'])
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserverMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventObserverMock->expects($this->at(0))->method('getData')->with('dirty_rules')->willReturn($flagMock);
        $flagMock->expects($this->once())->method('getState')->willReturn(1);
        $eventObserverMock->expects($this->at(1))->method('getData')->with('message')->willReturn($message);
        $this->messageManagerMock->expects($this->once())->method('addNoticeMessage')->with($message);
        $this->observer->execute($eventObserverMock);
    }
}
