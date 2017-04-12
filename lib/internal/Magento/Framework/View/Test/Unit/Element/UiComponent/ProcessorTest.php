<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ObserverInterface;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $component;

    /**
     * @var ObserverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observer;

    /**
     * @var Processor
     */
    protected $processor;

    protected function setUp()
    {
        $this->component = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->getMockForAbstractClass();
        $this->observer = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ObserverInterface::class)
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
        $component2 = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
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
