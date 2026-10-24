<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
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

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->messageManagerMock = $this->createMock(ManagerInterface::class);
        $objectManagerHelper = new ObjectManager($this);
        $this->observer = $objectManagerHelper->getObject(
            AddDirtyRulesNotice::class,
            [
                'messageManager' => $this->messageManagerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $message = "test";
        $flagMock = $this->createPartialMock(Flag::class, []);
        $flagMock->setState(1);
        $eventObserverMock = $this->createMock(Observer::class);
        $eventObserverMock
            ->method('getData')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['dirty_rules'] => $flagMock,
                ['message'] => $message
            });
        $this->messageManagerMock->expects($this->once())->method('addNoticeMessage')->with($message);
        $this->observer->execute($eventObserverMock);
    }
}
