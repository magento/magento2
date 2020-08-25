<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Event;
use Magento\Reports\Model\EventFactory;
use Magento\Reports\Observer\EventSaver;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for @see EventSaver
 */
class EventSaverTest extends TestCase
{
    const STUB_CUSTOMER_ID = 1;
    const STUB_VISITOR_ID = 2;
    const STUB_EVENT_TYPE_ID = 1;
    const STUB_OBJECT_ID = 1;
    const STUB_STORE_ID = 1;

    /**
     * @var Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var Visitor|MockObject
     */
    private $customerVisitorMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var EventFactory|MockObject
     */
    private $eventFactoryMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var EventSaver
     */
    private $eventSaver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->customerVisitorMock = $this->createMock(Visitor::class);

        $this->storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::STUB_STORE_ID);

        $this->eventMock = $this->createMock(Event::class);
        $this->eventFactoryMock = $this->createMock(EventFactory::class);
        $this->eventFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('setData')
            ->willReturnSelf();
        $this->eventMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $objectManagerHelper = new ObjectManager($this);
        $this->eventSaver = $objectManagerHelper->getObject(
            EventSaver::class,
            [
                '_storeManager' => $this->storeManagerMock,
                '_eventFactory' => $this->eventFactoryMock,
                '_customerSession' => $this->customerSessionMock,
                '_customerVisitor' => $this->customerVisitorMock
            ]
        );
    }

    /**
     * Test save with subject ID provided
     */
    public function testSaveWithSubject()
    {
        $subjectId = 5;
        $this->customerSessionMock->expects($this->never())
            ->method('isLoggedIn');
        $this->customerSessionMock->expects($this->never())
            ->method('getCustomerId');
        $this->customerVisitorMock->expects($this->never())
            ->method('getId');
        $this->eventSaver->save(self::STUB_EVENT_TYPE_ID, self::STUB_OBJECT_ID, $subjectId);
    }

    /**
     * Test save with no subject ID provided and customer is logged in
     */
    public function testSaveWithoutSubjectWhenLoggedIn()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::STUB_CUSTOMER_ID);
        $this->eventSaver->save(self::STUB_EVENT_TYPE_ID, self::STUB_OBJECT_ID, null);
    }

    /**
     * Test save with no subject ID provided and customer is not logged in
     */
    public function testSaveWithoutSubjectForGuest()
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->customerVisitorMock->expects($this->once())
            ->method('getId')
            ->willReturn(self::STUB_VISITOR_ID);
        $this->eventSaver->save(self::STUB_EVENT_TYPE_ID, self::STUB_OBJECT_ID, null);
    }
}
