<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Framework\Data\Form;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\View\LayoutInterface;
use Magento\Weee\Model\Tax;
use Magento\Weee\Observer\SetWeeeRendererInFormObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetWeeeRendererInFormObserverTest extends TestCase
{
    /**
     * Testable object
     *
     * @var SetWeeeRendererInFormObserver
     */
    private $observer;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layoutMock;

    /**
     * @var Tax|MockObject
     */
    private $taxModelMock;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->taxModelMock = $this->createMock(Tax::class);
        $this->observer = new SetWeeeRendererInFormObserver(
            $this->layoutMock,
            $this->taxModelMock
        );
    }

    /**
     * Test assigning a custom renderer for product create/edit form weee attribute element
     *
     * @return void
     */
    public function testExecute(): void
    {
        $attributes = new \ArrayIterator(['element_code_1', 'element_code_2']);
        /** @var Event|MockObject $eventMock */
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getForm'])
            ->getMock();

        /** @var Observer|MockObject $observerMock */
        $observerMock = $this->createMock(Observer::class);
        /** @var Form|MockObject $formMock */
        $formMock = $this->createMock(Form::class);

        $eventMock->expects($this->once())
            ->method('getForm')
            ->willReturn($formMock);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $this->taxModelMock->expects($this->once())
            ->method('getWeeeAttributeCodes')
            ->willReturn($attributes);
        $formMock->expects($this->exactly($attributes->count()))
            ->method('getElement')
            ->willReturnSelf();

        $this->observer->execute($observerMock);
    }
}
