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
namespace Magento\Framework;

use Magento\Framework\Event\Observer\Collection;
use Magento\Framework\Event\Observer;

/**
 * Class Event
 *
 * @package Magento\Framework
 */
class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event
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

    public function setUp()
    {
        $data = [
            'name' => 'ObserverName',
            'block' => 'testBlockName'
        ];
        $this->event = new Event($data);
        $this->observers = new Collection();
        $this->observer = new Observer($data);
        $this->observers->addObserver($this->observer);
    }

    protected function tearDown()
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
        $expected = 'Magento\Framework\Event\Observer\Collection';
        $actual = $this->event->getObservers()->removeObserverByName($data['name']);
        $this->assertInstanceOf($expected, $actual);
    }

    public function testDispatch()
    {
        $this->assertInstanceOf('Magento\Framework\Event', $this->event->dispatch());
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
