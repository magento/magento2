<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Test\Unit\Observer;

use \Magento\Framework\Event\Observer\Collection;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Collection
     */
    protected $observerCollection;

    protected function setUp(): void
    {
        $this->observerCollection = new Collection();
    }

    protected function tearDown(): void
    {
        $this->observerCollection = null;
    }

    /**
     * Create universal observer mock.
     * If event parameter is passed - observer mock expects dispatch method to be called with passed event.
     *
     * @param string $name
     * @param \Magento\Framework\Event | null $event
     * @return \Magento\Framework\Event\Observer |\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getObserverMock($name, $event = null)
    {
        $observer = $this->createMock(\Magento\Framework\Event\Observer::class);
        $observer->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        if ($event) {
            $observer->expects($this->once())
                ->method('dispatch')
                ->with($event);
        }
        return $observer;
    }

    public function testAddObserver()
    {
        $observer = $this->getObserverMock('test_observer');
        $this->observerCollection->addObserver($observer);
        $this->assertEquals($observer, $this->observerCollection->getObserverByName($observer->getName()));
    }

    public function testGetAllObservers()
    {
        $observer1 = $this->getObserverMock('test_observer1');
        $observer2 = $this->getObserverMock('test_observer2');

        $this->observerCollection->addObserver($observer1);
        $this->observerCollection->addObserver($observer2);

        $this->assertEquals(
            ['test_observer1' => $observer1, 'test_observer2' => $observer2],
            $this->observerCollection->getAllObservers()
        );
    }

    /**
     * @dataProvider observerNameProvider
     * @param string $name
     */
    public function testGetObserverByName($name)
    {
        $observer = $this->getObserverMock($name);
        $this->observerCollection->addObserver($observer);
        $this->assertEquals($observer, $this->observerCollection->getObserverByName($name));
    }

    /**
     * @return array
     */
    public function observerNameProvider()
    {
        return [
            ['simple_name'],
            ['1234567890'],
            ['~!@#$%^&*()_=-}{}'],
            ['DjnJ2139540___    asdf']
        ];
    }

    public function testRemoveObserverByName()
    {
        $observer1 = $this->getObserverMock('test_observer1');
        $observer2 = $this->getObserverMock('test_observer2');

        $this->observerCollection->addObserver($observer1);
        $this->observerCollection->addObserver($observer2);

        $this->assertEquals(
            ['test_observer1' => $observer1, 'test_observer2' => $observer2],
            $this->observerCollection->getAllObservers()
        );

        $this->observerCollection->removeObserverByName($observer2->getName());

        $this->assertEquals(['test_observer1' => $observer1], $this->observerCollection->getAllObservers());
    }

    public function testDispatch()
    {
        $eventMock = $this->createMock(\Magento\Framework\Event::class);

        $observer1 = $this->getObserverMock('test_observer1', $eventMock);
        $observer2 = $this->getObserverMock('test_observer2', $eventMock);

        $this->observerCollection->addObserver($observer1);
        $this->observerCollection->addObserver($observer2);

        $this->observerCollection->dispatch($eventMock);
    }
}
