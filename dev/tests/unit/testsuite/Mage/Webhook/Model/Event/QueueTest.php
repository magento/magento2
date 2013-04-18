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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Event_QueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Event_QueueTest
     */
    protected $_mockObject;

    public function setUp()
    {
        parent::setUp();

        $this->_mockObject = $this->getMock('Mage_Webhook_Model_Event_Queue',
                                            array('_getEventCollection'));
    }

    /**
     * Verify that a Mage_Webhook_Model_Event can be put into the queue.
     */
    public function testOfferSuccess()
    {
        $event = $this->getMockBuilder('Mage_Webhook_Model_Event')
                      ->disableOriginalConstructor()
                      ->setMethods(array('save', 'setStatus'))
                      ->getMock();
        $event->expects($this->once())
              ->method('setStatus')
              ->with($this->equalTo(Mage_Webhook_Model_Event::READY_TO_SEND));
        $event->expects($this->once())->method('save');

        $this->assertEquals(true, $this->_mockObject->offer($event));
    }

    /**
     * Verify that any instance of a class besides Mage_Webhook_Model_Event
     * will not successfully be saved to the queue.
     */
    public function testOfferFailure()
    {
        $event = $this->getMockBuilder('Mage_Webhook_Model_Event_Interface')
                      ->disableOriginalConstructor()
                      ->getMock();

        $this->assertEquals(false, $this->_mockObject->offer($event));
    }


    /**
     * Verify that if the queue is empty, there won't be any object returned from poll.
     */
    public function testPollNoObjects()
    {
        $collection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event_Collection')
                           ->disableOriginalConstructor()
                           ->setMethods(array('addFieldToFilter', 'getIterator'))
                           ->getMock();
        $this->_mockObject->expects($this->once())->method('_getEventCollection')
                          ->will($this->returnValue($collection));

        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());

        $iterator = $this->getMockBuilder('Iterator')
                         ->disableOriginalConstructor()
                         ->setMethods(array('valid', 'current', 'next', 'key', 'rewind'))
                         ->getMock();
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $iterator->expects($this->once())->method('valid')->will($this->returnValue(false));

        $this->assertNull($this->_mockObject->poll());
    }

    /**
     * Verify that if the queue has one element, that it will return.
     */
    public function testPollOneEvent()
    {
        $collection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event_Collection')
                           ->disableOriginalConstructor()
                           ->setMethods(array('addFieldToFilter', 'getIterator'))
                           ->getMock();
        $this->_mockObject->expects($this->once())->method('_getEventCollection')
                          ->will($this->returnValue($collection));

        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());

        $iterator = $this->getMockBuilder('Iterator')
                         ->disableOriginalConstructor()
                         ->setMethods(array('valid', 'current', 'next', 'key', 'rewind'))
                         ->getMock();
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $iterator->expects($this->once())->method('valid')->will($this->returnValue(true));

        $event = $this->getMockBuilder('Mage_Webhook_Model_Event')
                      ->setMethods(array('setStatus', 'save'))
                      ->disableOriginalConstructor()
                      ->getMock();
        $iterator->expects($this->once())->method('current')->will($this->returnValue($event));
        $iterator->expects($this->once())->method('next');

        $event->expects($this->once())->method('setStatus')->with(Mage_Webhook_Model_Event::PROCESSED);
        $event->expects($this->once())->method('save');

        $this->assertSame($event, $this->_mockObject->poll());
    }

    /**
     * Verify that if the queue has two element, that it will return.
     */
    public function testPollTwoEvent()
    {
        $collection = $this->getMockBuilder('Mage_Webhook_Model_Resource_Event_Collection')
                           ->disableOriginalConstructor()
                           ->setMethods(array('addFieldToFilter', 'getIterator'))
                           ->getMock();
        $this->_mockObject->expects($this->once())->method('_getEventCollection')
                          ->will($this->returnValue($collection));

        $collection->expects($this->once())->method('addFieldToFilter')->will($this->returnSelf());

        $iterator = $this->getMockBuilder('Iterator')
                         ->disableOriginalConstructor()
                         ->setMethods(array('valid', 'current', 'next', 'key', 'rewind'))
                         ->getMock();
        $collection->expects($this->once())->method('getIterator')->will($this->returnValue($iterator));

        $iterator->expects($this->exactly(3))->method('valid')->will($this->returnValue(true));
        $iterator->expects($this->exactly(2))->method('next');

        $event = $this->getMockBuilder('Mage_Webhook_Model_Event')
                      ->setMethods(array('setStatus', 'save'))
                      ->disableOriginalConstructor()
                      ->getMock();
        $event->expects($this->once())->method('setStatus')->with(Mage_Webhook_Model_Event::PROCESSED);
        $event->expects($this->once())->method('save');

        $event2 = $this->getMockBuilder('Mage_Webhook_Model_Event')
                      ->setMethods(array('setStatus', 'save'))
                      ->disableOriginalConstructor()
                      ->getMock();
        $event2->expects($this->once())->method('setStatus')->with(Mage_Webhook_Model_Event::PROCESSED);
        $event2->expects($this->once())->method('save');

        $iterator->expects($this->exactly(2))->method('current')->will($this->onConsecutiveCalls($event, $event2));

        $this->assertSame($event, $this->_mockObject->poll());
        $this->assertSame($event2, $this->_mockObject->poll());
    }
}
