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
namespace Magento\Framework\Message;

/**
 * \Magento\Framework\Message\Manager test case
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
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Message\Manager
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageMock;

    public function setUp()
    {
        $this->messagesFactory = $this->getMockBuilder(
            'Magento\Framework\Message\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $this->messageFactory = $this->getMockBuilder(
            'Magento\Framework\Message\Factory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();
        $this->session = $this->getMockBuilder(
            'Magento\Framework\Message\Session'
        )->disableOriginalConstructor()->setMethods(
            array('getData', 'setData')
        )->getMock();
        $this->logger = $this->getMockBuilder(
            'Magento\Framework\Logger'
        )->setMethods(
            array('logFile')
        )->disableOriginalConstructor()->getMock();
        $this->eventManager = $this->getMockBuilder(
            'Magento\Framework\Event\Manager'
        )->setMethods(
            array('dispatch')
        )->disableOriginalConstructor()->getMock();

        $this->messageMock = $this->getMock('Magento\Framework\Message\MessageInterface');
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $this->objectManager->getObject(
            'Magento\Framework\Message\Manager',
            array(
                'messagesFactory' => $this->messagesFactory,
                'messageFactory' => $this->messageFactory,
                'session' => $this->session,
                'eventManager' => $this->eventManager,
                'logger' => $this->logger
            )
        );
    }

    public function testGetDefaultGroup()
    {
        $this->assertEquals(ManagerInterface::DEFAULT_GROUP, $this->model->getDefaultGroup());

        $customDefaultGroup = 'some_group';
        $customManager = $this->objectManager->getObject(
            'Magento\Framework\Message\Manager',
            array('defaultGroup' => $customDefaultGroup)
        );
        $this->assertEquals($customDefaultGroup, $customManager->getDefaultGroup());
    }

    public function testGetMessages()
    {
        $messageCollection = $this->getMockBuilder(
            'Magento\Framework\Message\Collection'
        )->disableOriginalConstructor()->setMethods(
            array('addMessage')
        )->getMock();

        $this->messagesFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->will(
            $this->returnValue($messageCollection)
        );

        $this->session->expects(
            $this->at(0)
        )->method(
            'getData'
        )->with(
            ManagerInterface::DEFAULT_GROUP
        )->will(
            $this->returnValue(null)
        );
        $this->session->expects(
            $this->at(1)
        )->method(
            'setData'
        )->with(
            ManagerInterface::DEFAULT_GROUP,
            $messageCollection
        )->will(
            $this->returnValue($this->session)
        );
        $this->session->expects(
            $this->at(2)
        )->method(
            'getData'
        )->with(
            ManagerInterface::DEFAULT_GROUP
        )->will(
            $this->returnValue($messageCollection)
        );

        $this->eventManager->expects($this->never())->method('dispatch');

        $this->assertEquals($messageCollection, $this->model->getMessages());
    }

    public function testGetMessagesWithClear()
    {
        $messageCollection = $this->getMockBuilder(
            'Magento\Framework\Message\Collection'
        )->disableOriginalConstructor()->setMethods(
            array('addMessage', 'clear')
        )->getMock();

        $messageCollection->expects($this->once())->method('clear');

        $this->session->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            ManagerInterface::DEFAULT_GROUP
        )->will(
            $this->returnValue($messageCollection)
        );

        $this->eventManager->expects($this->once())->method('dispatch')->with('session_abstract_clear_messages');

        $this->assertEquals($messageCollection, $this->model->getMessages(true));
    }

    public function testAddException()
    {
        $exceptionMessage = 'exception message';
        $alternativeText = 'alternative text';
        $logText = "Exception message: {$exceptionMessage}\nTrace:";

        $messageError = $this->getMockBuilder(
            'Magento\Framework\Message\Error'
        )->setConstructorArgs(
            array('text' => $alternativeText)
        )->getMock();

        $this->messageFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            MessageInterface::TYPE_ERROR,
            $alternativeText
        )->will(
            $this->returnValue($messageError)
        );

        $this->logger->expects(
            $this->atLeastOnce()
        )->method(
            'logFile'
        )->with(
            $this->stringStartsWith($logText),
            \Zend_Log::DEBUG,
            \Magento\Framework\Logger::LOGGER_EXCEPTION
        );

        $messageCollection = $this->getMockBuilder(
            'Magento\Framework\Message\Collection'
        )->disableOriginalConstructor()->setMethods(
            array('addMessage')
        )->getMock();
        $messageCollection->expects($this->atLeastOnce())->method('addMessage')->with($messageError);

        $this->session->expects(
            $this->atLeastOnce()
        )->method(
            'getData'
        )->with(
            ManagerInterface::DEFAULT_GROUP
        )->will(
            $this->returnValue($messageCollection)
        );

        $exception = new \Exception($exceptionMessage);
        $this->assertEquals($this->model, $this->model->addException($exception, $alternativeText));
    }

    /**
     * @param string $type
     * @param string $methodName
     * @dataProvider addMessageDataProvider
     */
    public function testAddMessage($type, $methodName)
    {
        $this->assertFalse($this->model->hasMessages());
        $message = 'Message';
        $messageCollection = $this->getMock(
            'Magento\Framework\Message\Collection',
            array('addMessage'),
            array(),
            '',
            false
        );
        $this->session->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($messageCollection));
        $this->eventManager->expects($this->once())
            ->method('dispatch')->with('session_abstract_add_message');
        $this->messageFactory->expects($this->once())
            ->method('create')->with($type, $message)
            ->will($this->returnValue($this->messageMock));
        $this->model->$methodName($message, 'group');
        $this->assertTrue($this->model->hasMessages());
    }

    public function addMessageDataProvider()
    {
        return array(
            'error' => array(MessageInterface::TYPE_ERROR, 'addError'),
            'warning' => array(MessageInterface::TYPE_WARNING, 'addWarning'),
            'notice' => array(MessageInterface::TYPE_NOTICE, 'addNotice'),
            'success' => array(MessageInterface::TYPE_SUCCESS, 'addSuccess')
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $messages
     * @param string $text
     * @param string $expectation
     * @dataProvider addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider
     */
    public function testAddUniqueMessagesWhenMessagesImplementMessageInterface($messages, $text, $expectation)
    {
        $messageCollection =
            $this->getMock('Magento\Framework\Message\Collection', array('getItems', 'addMessage'), array(), '', false);
        $this->session->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($messageCollection));
        $messageCollection
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($this->messageMock)));
        $messageCollection->expects($this->$expectation())->method('addMessage');
        $this->messageMock->expects($this->once())->method('getText')->will($this->returnValue('text'));
        $messages->expects($this->once())->method('getText')->will($this->returnValue($text));
        $this->model->addUniqueMessages($messages);
    }

    public function addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider()
    {
        return array(
            'message_text_is_unique' => array(
                $this->getMock('Magento\Framework\Message\MessageInterface'),
                'text1',
                'once'
            ),
            'message_text_is_already_exist' => array(
                $this->getMock('Magento\Framework\Message\MessageInterface'),
                'text',
                'never'
            )
        );
    }

    /**
     * @param string|array $messages
     * @dataProvider addUniqueMessagesDataProvider
     */
    public function testAddUniqueMessages($messages)
    {
        $messageCollection =
            $this->getMock('Magento\Framework\Message\Collection', array('getItems', 'addMessage'), array(), '', false);
        $this->session->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($messageCollection));
        $messageCollection
            ->expects($this->any())
            ->method('getItems')
            ->will($this->returnValue(array('message')));
        $messageCollection->expects($this->never())->method('addMessage');
        $this->model->addUniqueMessages($messages);
    }

    public function addUniqueMessagesDataProvider()
    {
        return array(
            'messages_are_text' => array('message'),
            'messages_are_empty' =>array(array())
        );
    }

    public function testAddMessages()
    {
        $messageCollection = $this->getMock(
            'Magento\Framework\Message\Collection',
            array('getItems', 'addMessage'),
            array(),
            '',
            false
        );
        $this->session->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($messageCollection));
        $this->eventManager->expects($this->once())
            ->method('dispatch')->with('session_abstract_add_message');

        $messageCollection->expects($this->once())->method('addMessage')->with($this->messageMock);
        $this->model->addMessages(array($this->messageMock));
    }
}
