<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Reports\Model\Event;
use Magento\Weee\Block\Element\Weee\Tax;
use Magento\Weee\Observer\UpdateElementTypesObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Magento\Weee\Observer\UpdateElementTypesObserver
 */
class UpdateElementTypesObserverTest extends TestCase
{
    /*
     * Stub response type
     */
    const STUB_RESPONSE_TYPE = [];

    /**
     * Testable Object
     *
     * @var UpdateElementTypesObserver
     */
    private $observer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var DataObject|MockObject
     */
    private $responseMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createMock(Observer::class);

        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getResponse'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypes', 'setTypes'])
            ->getMock();

        $this->observer = $this->objectManager->getObject(UpdateElementTypesObserver::class);
    }

    /**
     * Test for execute(), covers test case to adding custom element type for attributes form
     */
    public function testRemoveProductUrlsFromStorage(): void
    {
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->responseMock
            ->expects($this->once())
            ->method('getTypes')
            ->willReturn(self::STUB_RESPONSE_TYPE);

        $this->responseMock
            ->expects($this->once())
            ->method('setTypes')
            ->with(['weee' => Tax::class]);

        $this->observer->execute($this->observerMock);
    }
}
