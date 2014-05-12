<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Event\Observer;

/**
 * Class CollectionTest
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Collection
     */
    protected $observerCollection;

    protected function setUp()
    {
        $this->observerCollection = new Collection();
    }

    protected function tearDown()
    {
        $this->observerCollection = null;
    }

    /**
     * Create universal observer mock.
     * If event parameter is passed - observer mock expects dispatch method to be called with passed event.
     *
     * @param string $name
     * @param \Magento\Framework\Event | null $event
     * @return \Magento\Framework\Event\Observer |\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getObserverMock($name, $event = null)
    {
        $observer = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $observer->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
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
        $eventMock = $this->getMock('Magento\Framework\Event', [], [], '', false);

        $observer1 = $this->getObserverMock('test_observer1', $eventMock);
        $observer2 = $this->getObserverMock('test_observer2', $eventMock);

        $this->observerCollection->addObserver($observer1);
        $this->observerCollection->addObserver($observer2);

        $this->observerCollection->dispatch($eventMock);
    }
}
