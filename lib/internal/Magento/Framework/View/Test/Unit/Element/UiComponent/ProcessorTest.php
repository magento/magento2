<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\ObserverInterface;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var UiComponentInterface|MockObject
     */
    protected $component;

    /**
     * @var ObserverInterface|MockObject
     */
    protected $observer;

    /**
     * @var Processor
     */
    protected $processor;

    protected function setUp(): void
    {
        $this->component = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();
        $this->observer = $this->getMockBuilder(ObserverInterface::class)
            ->getMockForAbstractClass();
        $this->processor = new Processor();
    }

    public function testRegisterGetComponents()
    {
        $this->assertCount(0, $this->processor->getComponents());
        $this->processor->register($this->component);
        $this->assertCount(1, $this->processor->getComponents());
    }

    public function testAttachAndNotify()
    {
        $type = 'test_type';
        $this->component->expects($this->any())
            ->method('getComponentName')
            ->willReturn($type);
        $this->observer->expects($this->any())
            ->method('update')
            ->with($this->component);
        /** @var UiComponentInterface $component2 */
        $component2 = $this->getMockBuilder(UiComponentInterface::class)
            ->getMockForAbstractClass();
        $component2->expects($this->any())
            ->method('getComponentName')
            ->willReturn('other_type');

        $this->processor->register($this->component);
        $this->processor->register($component2);
        $this->processor->attach($type, $this->observer);
        $this->processor->notify($type);
    }

    public function testDetach()
    {
        $this->processor->detach('unexists_type', $this->observer);
        $this->processor->attach('some_type', $this->observer);
        $this->processor->notify('unexists_type');
        $this->processor->detach('some_type', $this->observer);
    }
}
