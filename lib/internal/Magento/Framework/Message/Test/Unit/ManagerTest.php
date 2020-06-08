<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\ExceptionMessageLookupFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Session;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * \Magento\Framework\Message\Manager test case
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ManagerTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Factory|MockObject
     */
    protected $messageFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $messagesFactory;

    /**
     * @var Session|MockObject
     */
    protected $session;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManager;

    /**
     * @var Manager
     */
    protected $model;

    /**
     * @var MessageInterface|MockObject
     */
    protected $messageMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var ExceptionMessageLookupFactory|MockObject
     */
    private $exceptionMessageFactory;

    protected function setUp(): void
    {
        $this->messagesFactory = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageFactory = $this->getMockBuilder(
            Factory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(
            Session::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                ['getData', 'setData']
            )
            ->getMock();
        $this->eventManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->exceptionMessageFactory = $this->getMockBuilder(
            ExceptionMessageLookupFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageMock = $this->getMockForAbstractClass(MessageInterface::class);
        $this->objectManager = new ObjectManager($this);
        $this->model = new Manager(
            $this->session,
            $this->messageFactory,
            $this->messagesFactory,
            $this->eventManager,
            $this->logger,
            Manager::DEFAULT_GROUP,
            $this->exceptionMessageFactory
        );
    }

    public function testGetDefaultGroup()
    {
        $this->assertEquals(Manager::DEFAULT_GROUP, $this->model->getDefaultGroup());
    }

    public function testGetMessages()
    {
        $messageCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
            ['addMessage']
        )->getMock();

        $this->messagesFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->willReturn(
            $messageCollection
        );

        $this->session->expects(
            $this->at(0)
        )->method(
            'getData'
        )->with(
            Manager::DEFAULT_GROUP
        )->willReturn(
            null
        );
        $this->session->expects(
            $this->at(1)
        )->method(
            'setData'
        )->with(
            Manager::DEFAULT_GROUP,
            $messageCollection
        )->willReturn(
            $this->session
        );
        $this->session->expects(
            $this->at(2)
        )->method(
            'getData'
        )->with(
            Manager::DEFAULT_GROUP
        )->willReturn(
            $messageCollection
        );

        $this->eventManager->expects($this->never())->method('dispatch');

        $this->assertEquals($messageCollection, $this->model->getMessages());
    }

    public function testGetMessagesWithClear()
    {
        $messageCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
            ['addMessage', 'clear']
        )->getMock();

        $messageCollection->expects($this->once())->method('clear');

        $this->session->expects(
            $this->any()
        )->method(
            'getData'
        )->with(
            Manager::DEFAULT_GROUP
        )->willReturn(
            $messageCollection
        );

        $this->eventManager->expects($this->once())->method('dispatch')->with('session_abstract_clear_messages');

        $this->assertEquals($messageCollection, $this->model->getMessages(true));
    }

    public function testAddExceptionWithAlternativeText()
    {
        $exceptionMessage = 'exception message';
        $alternativeText = 'alternative text';

        $this->logger->expects(
            $this->once()
        )->method(
            'critical'
        );

        $messageError = $this->getMockBuilder(
            Error::class
        )->setConstructorArgs(
            ['text' => $alternativeText]
        )->getMock();

        $this->messageFactory->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            MessageInterface::TYPE_ERROR,
            $alternativeText
        )->willReturn(
            $messageError
        );

        $messageCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
            ['addMessage']
        )->getMock();
        $messageCollection->expects($this->atLeastOnce())->method('addMessage')->with($messageError);

        $this->session->expects(
            $this->atLeastOnce()
        )->method(
            'getData'
        )->with(
            Manager::DEFAULT_GROUP
        )->willReturn(
            $messageCollection
        );

        $exception = new \Exception($exceptionMessage);
        $this->assertEquals($this->model, $this->model->addException($exception, $alternativeText));
    }

    public function testAddExceptionRenderable()
    {
        $exceptionMessage = 'exception message';
        $exception = new \Exception($exceptionMessage);

        $this->logger->expects(
            $this->once()
        )->method(
            'critical'
        );

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $this->messageFactory->expects(
            $this->never()
        )->method(
            'create'
        );

        $this->exceptionMessageFactory->expects(
            $this->once()
        )->method(
            'createMessage'
        )->with(
            $exception
        )->willReturn(
            $message
        );

        $messageCollection = $this->getMockBuilder(
            Collection::class
        )->disableOriginalConstructor()
            ->setMethods(
            ['addMessage']
        )->getMock();
        $messageCollection->expects($this->atLeastOnce())->method('addMessage')->with($message);

        $this->session->expects(
            $this->atLeastOnce()
        )->method(
            'getData'
        )->with(
            Manager::DEFAULT_GROUP
        )->willReturn(
            $messageCollection
        );

        $this->assertEquals($this->model, $this->model->addExceptionMessage($exception));
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
        $messageCollection = $this->createPartialMock(Collection::class, ['addMessage']);
        $this->session->expects($this->any())
            ->method('getData')
            ->willReturn($messageCollection);
        $this->eventManager->expects($this->once())
            ->method('dispatch')->with('session_abstract_add_message');
        $this->messageFactory->expects($this->once())
            ->method('create')->with($type, $message)
            ->willReturn($this->messageMock);
        $this->model->$methodName($message, 'group');
        $this->assertTrue($this->model->hasMessages());
    }

    /**
     * @return array
     */
    public function addMessageDataProvider()
    {
        return [
            'error' => [MessageInterface::TYPE_ERROR, 'addError'],
            'warning' => [MessageInterface::TYPE_WARNING, 'addWarning'],
            'notice' => [MessageInterface::TYPE_NOTICE, 'addNotice'],
            'success' => [MessageInterface::TYPE_SUCCESS, 'addSuccess']
        ];
    }

    /**
     * @param MockObject $messages
     * @param string $expectation
     * @dataProvider addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider
     */
    public function testAddUniqueMessagesWhenMessagesImplementMessageInterface($messages, $expectation)
    {
        $messageCollection =
            $this->createPartialMock(Collection::class, ['getItems', 'addMessage']);
        $this->session->expects($this->any())
            ->method('getData')
            ->willReturn($messageCollection);
        $messageCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([new TestingMessage('text')]);
        $messageCollection->expects($this->$expectation())->method('addMessage');
        $this->model->addUniqueMessages([$messages]);
    }

    /**
     * @return array
     */
    public function addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider()
    {
        return [
            'message_text_is_unique' => [
                new TestingMessage('text1'),
                'once',
            ],
            'message_text_already_exists' => [
                new TestingMessage('text'),
                'never',
            ]
        ];
    }

    /**
     * @param string|array $messages
     * @dataProvider addUniqueMessagesDataProvider
     */
    public function testAddUniqueMessages($messages)
    {
        $messageCollection =
            $this->createPartialMock(Collection::class, ['getItems', 'addMessage']);
        $this->session->expects($this->any())
            ->method('getData')
            ->willReturn($messageCollection);
        $messageCollection
            ->expects($this->any())
            ->method('getItems')
            ->willReturn(['message']);
        $messageCollection->expects($this->never())->method('addMessage');
        $this->model->addUniqueMessages($messages);
    }

    /**
     * @return array
     */
    public function addUniqueMessagesDataProvider()
    {
        return [
            'messages_are_text' => [['message']],
            'messages_are_empty' => [[]]
        ];
    }

    public function testAddMessages()
    {
        $messageCollection =
            $this->createPartialMock(Collection::class, ['getItems', 'addMessage']);
        $this->session->expects($this->any())
            ->method('getData')
            ->willReturn($messageCollection);
        $this->eventManager->expects($this->once())
            ->method('dispatch')->with('session_abstract_add_message');

        $messageCollection->expects($this->once())->method('addMessage')->with($this->messageMock);
        $this->model->addMessages([$this->messageMock]);
    }
}
