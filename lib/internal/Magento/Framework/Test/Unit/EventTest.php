<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;

use Magento\Framework\Event\Observer\Collection;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * @var Collection
     */
    protected $observers;

    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $observer;

    protected function setUp(): void
    {
        $data = [
            'name' => 'ObserverName',
            'block' => 'testBlockName',
        ];
        $this->event = new Event($data);
        $this->observers = new Collection();
        $this->observer = new Observer($data);
        $this->observers->addObserver($this->observer);
    }

    protected function tearDown(): void
    {
        unset($this->event);
    }

    public function testGetObservers()
    {
        $this->event->addObserver($this->observer);
        $expected = $this->observers;
        $result = $this->event->getObservers();
        $this->assertEquals($expected, $result);
    }

    public function testAddObservers()
    {
        $data = ['name' => 'Add New Observer'];
        $observer = new Observer($data);
        $this->event->addObserver($observer);
        $actual = $this->event->getObservers()->getObserverByName($data['name']);
        $this->assertSame($observer, $actual);
    }

    public function testRemoveObserverByName()
    {
        $data = [
            'name' => 'ObserverName',
        ];
        $this->event->addObserver($this->observer);
        $expected = Collection::class;
        $actual = $this->event->getObservers()->removeObserverByName($data['name']);
        $this->assertInstanceOf($expected, $actual);
    }

    public function testDispatch()
    {
        $this->assertInstanceOf(Event::class, $this->event->dispatch());
    }

    public function testGetName()
    {
        $data = 'ObserverName';
        $this->assertEquals($data, $this->event->getName());
        $this->event = new Event();
        $this->assertNull($this->event->getName());
    }

    public function testGetBlock()
    {
        $block = 'testBlockName';
        $this->assertEquals($block, $this->event->getBlock());
    }
}
