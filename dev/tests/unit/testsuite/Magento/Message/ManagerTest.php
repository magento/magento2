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

namespace Magento\Message;

/**
 * \Magento\Message\Manager test case
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageFactory;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messagesFactory;

    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Message\Manager
     */
    protected $model;

    public function setUp()
    {
        $this->messagesFactory = $this->getMockBuilder('Magento\Message\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->messageFactory = $this->getMockBuilder('Magento\Message\Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('create'))
            ->getMock();
        $this->session = $this->getMockBuilder('Magento\Message\Session')
            ->disableOriginalConstructor()
            ->setMethods(array('getData', 'setData'))
            ->getMock();
        $this->logger = $this->getMockBuilder('Magento\Logger')
            ->setMethods(array('logFile'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMockBuilder('Magento\Event\Manager')
            ->setMethods(array('dispatch'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject('Magento\Message\Manager', array(
            'messagesFactory' => $this->messagesFactory,
            'messageFactory' => $this->messageFactory,
            'session' => $this->session,
            'eventManager' => $this->eventManager,
            'logger' => $this->logger
        ));
    }

    public function testGetDefaultGroup()
    {
        $this->assertEquals(ManagerInterface::DEFAULT_GROUP, $this->model->getDefaultGroup());

        $customDefaultGroup = 'some_group';
        $customManager = $this->objectManager->getObject(
            'Magento\Message\Manager',
            array('defaultGroup' => $customDefaultGroup)
        );
        $this->assertEquals($customDefaultGroup, $customManager->getDefaultGroup());
    }

    public function testGetMessages()
    {
        $messageCollection = $this->getMockBuilder('Magento\Message\Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addMessage'))
            ->getMock();

        $this->messagesFactory->expects($this->atLeastOnce())
            ->method('create')
            ->will($this->returnValue($messageCollection));

        $this->session->expects($this->at(0))
            ->method('getData')
            ->with(ManagerInterface::DEFAULT_GROUP)
            ->will($this->returnValue(null));
        $this->session->expects($this->at(1))
            ->method('setData')
            ->with(ManagerInterface::DEFAULT_GROUP, $messageCollection)
            ->will($this->returnValue($this->session));
        $this->session->expects($this->at(2))
            ->method('getData')
            ->with(ManagerInterface::DEFAULT_GROUP)
            ->will($this->returnValue($messageCollection));

        $this->eventManager->expects($this->never())
            ->method('dispatch');

         $this->assertEquals($messageCollection, $this->model->getMessages());
    }

    public function testGetMessagesWithClear()
    {
        $messageCollection = $this->getMockBuilder('Magento\Message\Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addMessage', 'clear'))
            ->getMock();

        $messageCollection->expects($this->once())
            ->method('clear');

        $this->session->expects($this->any())
            ->method('getData')
            ->with(ManagerInterface::DEFAULT_GROUP)
            ->will($this->returnValue($messageCollection));

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('core_session_abstract_clear_messages');

        $this->assertEquals($messageCollection, $this->model->getMessages(true));
    }

    public function testAddException()
    {
        $exceptionMessage = 'exception message';
        $alternativeText = 'alternative text';
        $logText = "Exception message: {$exceptionMessage}\nTrace:";

        $messageError = $this->getMockBuilder('Magento\Message\Error')
            ->setConstructorArgs(array('text' => $alternativeText))
            ->getMock();

        $this->messageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR, $alternativeText)
            ->will($this->returnValue($messageError));

        $this->logger->expects($this->atLeastOnce())
            ->method('logFile')
            ->with($this->stringStartsWith($logText), \Zend_Log::DEBUG, \Magento\Logger::LOGGER_EXCEPTION);

        $messageCollection = $this->getMockBuilder('Magento\Message\Collection')
            ->disableOriginalConstructor()
            ->setMethods(array('addMessage'))
            ->getMock();
        $messageCollection->expects($this->atLeastOnce())
            ->method('addMessage')
            ->with($messageError);

        $this->session->expects($this->atLeastOnce())
            ->method('getData')
            ->with(ManagerInterface::DEFAULT_GROUP)->will($this->returnValue($messageCollection));

        $exception = new \Exception($exceptionMessage);
        $this->assertEquals($this->model, $this->model->addException($exception, $alternativeText));
    }
}
