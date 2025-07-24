<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\Manager;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Framework\View\Element\Messages;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Framework\View\Element\Messages
 */
class MessagesTest extends TestCase
{
    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var Factory|MockObject
     */
    protected $messageFactory;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactory;

    /**
     * @var InterpretationStrategyInterface|MockObject
     */
    protected $messageInterpretationStrategy;

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->collectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageFactory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageInterpretationStrategy = $this->createMock(
            InterpretationStrategyInterface::class
        );

        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->messages = $objectManager->getObject(
            Messages::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'messageFactory' => $this->messageFactory,
                'interpretationStrategy' => $this->messageInterpretationStrategy,
                'escaper' => $this->escaperMock
            ]
        );
    }

    /**
     * @return MockObject|Collection
     */
    protected function initMessageCollection(): Collection
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        return $collection;
    }

    /**
     * @return void
     */
    public function testSetMessages(): void
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactory->expects($this->never())->method('create');

        $this->messages->setMessages($collection);
        $this->assertSame($collection, $this->messages->getMessageCollection());
    }

    /**
     * @return void
     */
    public function testGetMessageCollection(): void
    {
        $collection = $this->initMessageCollection();

        $this->assertSame($collection, $this->messages->getMessageCollection());
    }

    /**
     * @return void
     */
    public function testAddMessages(): void
    {
        $messageOne = $this->getMockForAbstractClass(MessageInterface::class);
        $messageTwo = $this->getMockForAbstractClass(MessageInterface::class);

        $arrayMessages = [$messageOne, $messageTwo];

        $collection = $this->initMessageCollection();

        $collection
            ->method('addMessage')
            ->willReturnCallback(
                function ($arg) use ($messageOne, $messageTwo) {
                    if ($arg == $messageOne || $arg == $messageTwo) {
                        return null;
                    }
                }
            );

        $collectionForAdd = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionForAdd->expects($this->atLeastOnce())
            ->method('getItems')
            ->willReturn($arrayMessages);

        $this->assertSame($this->messages, $this->messages->addMessages($collectionForAdd));
    }

    /**
     * @return void
     */
    public function testAddMessage(): void
    {
        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $collection = $this->initMessageCollection();

        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addMessage($message));
    }

    /**
     * @return void
     */
    public function testAddError(): void
    {
        $messageText = 'Some message error text';

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_ERROR, $messageText)
            ->willReturn($message);

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addError($messageText));
    }

    /**
     * @return void
     */
    public function testAddWarning(): void
    {
        $messageText = 'Some message warning text';

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_WARNING, $messageText)
            ->willReturn($message);

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addWarning($messageText));
    }

    /**
     * @return void
     */
    public function testAddNotice(): void
    {
        $messageText = 'Some message notice text';

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_NOTICE, $messageText)
            ->willReturn($message);

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addNotice($messageText));
    }

    /**
     * @return void
     */
    public function testAddSuccess(): void
    {
        $messageText = 'Some message success text';

        $message = $this->getMockForAbstractClass(MessageInterface::class);

        $this->messageFactory->expects($this->once())
            ->method('create')
            ->with(MessageInterface::TYPE_SUCCESS, $messageText)
            ->willReturn($message);

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('addMessage')
            ->with($message);

        $this->assertSame($this->messages, $this->messages->addSuccess($messageText));
    }

    /**
     * @return void
     */
    public function testGetMessagesByType(): void
    {
        $messageType = MessageInterface::TYPE_SUCCESS;
        $resultMessages = [$this->getMockForAbstractClass(MessageInterface::class)];

        $collection = $this->initMessageCollection();
        $collection->expects($this->once())
            ->method('getItemsByType')
            ->with($messageType)
            ->willReturn($resultMessages);

        $this->assertSame($resultMessages, $this->messages->getMessagesByType($messageType));
    }

    /**
     * @return void
     */
    public function testGetMessageTypes(): void
    {
        $types = [
            MessageInterface::TYPE_ERROR,
            MessageInterface::TYPE_WARNING,
            MessageInterface::TYPE_NOTICE,
            MessageInterface::TYPE_SUCCESS
        ];
        $this->assertEquals($types, $this->messages->getMessageTypes());
    }

    /**
     * @return void
     */
    public function testGetCacheKeyInfo(): void
    {
        $emptyMessagesCacheKey = ['storage_types' => ''];
        $this->assertEquals($emptyMessagesCacheKey, $this->messages->getCacheKeyInfo());

        $messagesCacheKey = ['storage_types' => 'default'];
        $this->messages->addStorageType(Manager::DEFAULT_GROUP);
        $this->assertEquals($messagesCacheKey, $this->messages->getCacheKeyInfo());
    }

    /**
     * @return void
     */
    public function testGetGroupedHtml(): void
    {
        $this->messages->setNameInLayout('nameInLayout');

        $resultHtml = '<div class="messages">';
        $resultHtml .= '<div class="message message-error error"><div data-ui-id="nameinlayout-message-error" >';
        $resultHtml .= 'Error message without HTML!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-warning warning"><div data-ui-id="nameinlayout-message-warning" >';
        $resultHtml .= 'Warning message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-warning warning"><div data-ui-id="nameinlayout-message-warning" >';
        $resultHtml .= 'Warning message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-notice notice"><div data-ui-id="nameinlayout-message-notice" >';
        $resultHtml .= 'Notice message without HTML!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-notice notice"><div data-ui-id="nameinlayout-message-notice" >';
        $resultHtml .= 'Notice message without HTML!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-notice notice"><div data-ui-id="nameinlayout-message-notice" >';
        $resultHtml .= 'Notice message without HTML!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-success success"><div data-ui-id="nameinlayout-message-success" >';
        $resultHtml .= 'Success message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-success success"><div data-ui-id="nameinlayout-message-success" >';
        $resultHtml .= 'Success message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-success success"><div data-ui-id="nameinlayout-message-success" >';
        $resultHtml .= 'Success message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '<div class="message message-success success"><div data-ui-id="nameinlayout-message-success" >';
        $resultHtml .= 'Success message with <strong>HTML</strong>!';
        $resultHtml .= '</div></div>';
        $resultHtml .= '</div>';

        $errorMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();
        $warningMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();
        $noticeMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();
        $successMock = $this->getMockBuilder(MessageInterface::class)
            ->getMockForAbstractClass();

        $this->messageInterpretationStrategy->expects(static::any())
            ->method('interpret')
            ->willReturnMap(
                [
                    [$errorMock, 'Error message without HTML!'],
                    [$warningMock, 'Warning message with <strong>HTML</strong>!'],
                    [$noticeMock, 'Notice message without HTML!'],
                    [$successMock, 'Success message with <strong>HTML</strong>!']
                ]
            );

        $collectionMock = $this->initMessageCollection();
        $collectionMock->expects($this->exactly(4))
            ->method('getItemsByType')
            ->willReturnMap(
                [
                    [MessageInterface::TYPE_ERROR, [$errorMock]],
                    [MessageInterface::TYPE_WARNING, [$warningMock, $warningMock]],
                    [MessageInterface::TYPE_NOTICE, [$noticeMock, $noticeMock, $noticeMock]],
                    [MessageInterface::TYPE_SUCCESS, [$successMock, $successMock, $successMock, $successMock]]
                ]
            );

        $this->escaperMock->expects($this->any())
            ->method('escapeHtmlAttr')
            ->willReturnCallback(
                function ($string) {
                    return $string;
                }
            );

        $this->assertEquals($resultHtml, $this->messages->getGroupedHtml());
    }
}
