<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Message\Test\Unit;

use Exception;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->messagesFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageFactory = $this->getMockBuilder(
            Factory::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->addMethods(['setData'])
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

    /**
     * @return void
     */
    public function testGetDefaultGroup(): void
    {
        $this->assertEquals(Manager::DEFAULT_GROUP, $this->model->getDefaultGroup());
    }

    /**
     * @return void
     */
    public function testGetMessages(): void
    {
        $messageCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMessage'])->getMock();

        $this->messagesFactory->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($messageCollection);

        $this->session
            ->method('getData')
            ->willReturnCallback(
                function ($arg1) use ($messageCollection) {
                    static $callCount = 0;
                    if ($callCount == 0 && $arg1 == Manager::DEFAULT_GROUP) {
                        $callCount++;
                        return null;
                    } elseif ($callCount == 1 && $arg1 == Manager::DEFAULT_GROUP) {
                        $callCount++;
                        return $messageCollection;
                    }
                }
            );
        $this->session
            ->method('setData')
            ->with(Manager::DEFAULT_GROUP, $messageCollection)
            ->willReturn($this->session);

        $this->eventManager->expects($this->never())->method('dispatch');

        $this->assertEquals($messageCollection, $this->model->getMessages());
    }

    /**
     * @return void
     */
    public function testGetMessagesWithClear(): void
    {
        $messageCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMessage', 'clear'])->getMock();

        $messageCollection->expects($this->once())->method('clear');

        $this->session->expects($this->any())
            ->method('getData')
            ->with(Manager::DEFAULT_GROUP)
            ->willReturn($messageCollection);

        $this->eventManager->expects($this->once())->method('dispatch')->with('session_abstract_clear_messages');

        $this->assertEquals($messageCollection, $this->model->getMessages(true));
    }

    /**
     * @return void
     */
    public function testAddExceptionWithAlternativeText(): void
    {
        $exceptionMessage = 'exception message';
        $alternativeText = 'alternative text';

        $this->logger->expects($this->once())
            ->method('critical');

        $messageError = $this->getMockBuilder(Error::class)
            ->setConstructorArgs(['text' => $alternativeText])
            ->getMock();

        $this->messageFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR, $alternativeText)
            ->willReturn($messageError);

        $messageCollection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()
            ->onlyMethods(['addMessage'])->getMock();
        $messageCollection->expects($this->atLeastOnce())->method('addMessage')->with($messageError);

        $this->session->expects($this->atLeastOnce())
            ->method('getData')
            ->with(Manager::DEFAULT_GROUP)
            ->willReturn($messageCollection);

        $exception = new Exception($exceptionMessage);
        $this->assertEquals($this->model, $this->model->addException($exception, $alternativeText));
    }

    /**
     * @return void
     */
    public function testAddExceptionRenderable(): void
    {
        $exceptionMessage = 'exception message';
        $exception = new Exception($exceptionMessage);
        $this->logger->expects($this->once())->method('critical');
        $message = $this->getMockForAbstractClass(MessageInterface::class);
        $this->messageFactory->expects($this->never())->method('create');

        $this->exceptionMessageFactory->expects($this->once())
            ->method('createMessage')
            ->with($exception)
            ->willReturn($message);

        $messageCollection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()
            ->onlyMethods(['addMessage'])->getMock();
        $messageCollection->expects($this->atLeastOnce())->method('addMessage')->with($message);

        $this->session->expects($this->atLeastOnce())
            ->method('getData')
            ->with(Manager::DEFAULT_GROUP)
            ->willReturn($messageCollection);

        $this->assertEquals($this->model, $this->model->addExceptionMessage($exception));
    }

    /**
     * @param string $type
     * @param string $methodName
     *
     * @return void
     * @dataProvider addMessageDataProvider
     */
    public function testAddMessage($type, $methodName): void
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
    public static function addMessageDataProvider(): array
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
     *
     * @return void
     * @dataProvider addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider
     */
    public function testAddUniqueMessagesWhenMessagesImplementMessageInterface($messages, $expectation): void
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
    public static function addUniqueMessagesWhenMessagesImplementMessageInterfaceDataProvider(): array
    {
        return [
            'message_text_is_unique' => [
                new TestingMessage('text1'),
                'once',
            ],
            'message_text_already_exists' => [
                new TestingMessage('text'),
                'never'
            ]
        ];
    }

    /**
     * @param string|array $messages
     *
     * @return void
     * @dataProvider addUniqueMessagesDataProvider
     */
    public function testAddUniqueMessages($messages): void
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
    public static function addUniqueMessagesDataProvider(): array
    {
        return [
            'messages_are_text' => [['message']],
            'messages_are_empty' => [[]]
        ];
    }

    /**
     * @return void
     */
    public function testAddMessages(): void
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
