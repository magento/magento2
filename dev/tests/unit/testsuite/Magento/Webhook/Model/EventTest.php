<?php
/**
 * \Magento\Webhook\Model\Event
 *
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * A string used for testing time formats.  Any string will do but it should look something like this.
     */
    const SOME_FORMATTED_TIME = '2013-07-10 12:35:28';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mockContext;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Webhook\Model\Event
     */
    protected $_event;

    protected function setUp()
    {
        $this->_mockContext = $this->getMockBuilder('Magento\Core\Model\Context')
            ->disableOriginalConstructor()
            ->getMock();

        $mockEventManager = $this->getMockBuilder('Magento\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_mockContext->expects($this->once())
            ->method('getEventDispatcher')
            ->will($this->returnValue($mockEventManager));

        $coreRegistry = $this->getMock('Magento\Core\Model\Registry', array(), array(), '', false);

        $this->_event = $this->getMockBuilder('Magento\Webhook\Model\Event')
            ->setConstructorArgs(array($this->_mockContext, $coreRegistry))
            ->setMethods(
                array('_init', 'isDeleted', 'isObjectNew', 'getId', '_hasModelChanged', '_getResource')
            )
            ->getMock();
    }

    public function testBeforeSaveNewObject()
    {
        $this->_mockMethodsForSave();

        $this->_event->expects($this->any())
            ->method('isObjectNew')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $mockResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Event')
            ->disableOriginalConstructor()
            ->getMock();

        $mockResource->expects($this->any())
            ->method('formatDate')
            ->with($this->equalTo(true))
            ->will($this->returnValue(self::SOME_FORMATTED_TIME));

        // needed for 'save' method
        $mockResource->expects($this->once())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_event->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->assertSame($this->_event, $this->_event->save());

        $this->assertSame(self::SOME_FORMATTED_TIME, $this->_event->getCreatedAt());
        $this->assertNull($this->_event->getUpdatedAt());
        $this->assertSame(\Magento\PubSub\EventInterface::STATUS_READY_TO_SEND, $this->_event->getStatus());
    }

    /**
     * This method mocks all the calls required in the "save" method, such that 'beforeSave' will be called
     */
    protected function _mockMethodsForSave()
    {
        $this->_event->expects($this->once())
            ->method('isDeleted')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $this->_event->expects($this->once())
            ->method('_hasModelChanged')
            ->withAnyParameters()
            ->will($this->returnValue(true));
    }

    public function testBeforeSaveOldObject()
    {
        $this->_mockMethodsForSave();

        $this->_event->expects($this->any())
            ->method('isObjectNew')
            ->withAnyParameters()
            ->will($this->returnValue(false));

        $this->_event->expects($this->any())
            ->method('getId')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $mockResource = $this->getMockBuilder('Magento\Webhook\Model\Resource\Event')
            ->disableOriginalConstructor()
            ->getMock();
        $mockResource->expects($this->any())
            ->method('formatDate')
            ->with($this->equalTo(true))
            ->will($this->returnValue(self::SOME_FORMATTED_TIME));

        // needed for 'save' method
        $mockResource->expects($this->once())
            ->method('addCommitCallback')
            ->withAnyParameters()
            ->will($this->returnSelf());

        $this->_event->expects($this->any())
            ->method('_getResource')
            ->withAnyParameters()
            ->will($this->returnValue($mockResource));

        $this->assertSame($this->_event, $this->_event->save());

        $this->assertSame(self::SOME_FORMATTED_TIME, $this->_event->getUpdatedAt());
        $this->assertNull($this->_event->getCreatedAt());
        $this->assertSame(\Magento\PubSub\EventInterface::STATUS_READY_TO_SEND, $this->_event->getStatus());
    }

    public function testGettersAndSetters()
    {
        $this->assertEquals(array(), $this->_event->getBodyData());
        $data = array('some', 'random', 'data');
        $this->_event->setBodyData($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getBodyData());

        $this->assertEquals(array(), $this->_event->getHeaders());
        $this->_event->setHeaders($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getHeaders());

        $this->assertSame(\Magento\PubSub\EventInterface::STATUS_READY_TO_SEND, $this->_event->getStatus());
        $this->_event->setStatus($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getStatus());

        $this->assertNull($this->_event->getTopic());
        $this->_event->setTopic($data);
        $this->assertTrue($this->_event->hasDataChanges());
        $this->assertEquals($data, $this->_event->getTopic());
    }
}
