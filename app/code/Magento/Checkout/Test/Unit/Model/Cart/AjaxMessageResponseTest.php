<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Model\Cart;

use Magento\Checkout\Model\Cart\AjaxMessageResponse;
use Magento\Framework\Message\Collection;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\Error;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Message\Notice;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AjaxMessageResponseTest extends TestCase
{
    /**
     * @var ManagerInterface&MockObject
     */
    private ManagerInterface $messageManager;

    /**
     * @var LayoutFactory&MockObject
     */
    private LayoutFactory $layoutFactory;

    /**
     * @var CollectionFactory&MockObject
     */
    private CollectionFactory $messageCollectionFactory;

    /**
     * @var AjaxMessageResponse
     */
    private AjaxMessageResponse $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->layoutFactory = $this->createMock(LayoutFactory::class);
        $this->messageCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->model = $objectManager->getObject(
            AjaxMessageResponse::class,
            [
                'messageManager' => $this->messageManager,
                'layoutFactory' => $this->layoutFactory,
                'messageCollectionFactory' => $this->messageCollectionFactory,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetInlineErrorMessagesReturnsNullWhenNoErrors(): void
    {
        $messages = $this->createMock(Collection::class);
        $messages->expects($this->exactly(2))
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, []],
                [MessageInterface::TYPE_NOTICE, []],
            ]);

        $errorMessages = $this->createMock(Collection::class);
        $errorMessages->expects($this->once())->method('getCount')->willReturn(0);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messages);
        $this->messageCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($errorMessages);
        $this->layoutFactory->expects($this->never())->method('create');

        $this->assertNull($this->model->getInlineErrorMessages(true));
    }

    /**
     * @return void
     */
    public function testGetInlineErrorMessagesRendersErrors(): void
    {
        $error = new Error();
        $error->setText('Product that you are trying to add is not available.');

        $messages = $this->createMock(Collection::class);
        $messages->expects($this->exactly(2))
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, [$error]],
                [MessageInterface::TYPE_NOTICE, []],
            ]);

        $errorMessages = $this->createMock(Collection::class);
        $errorMessages->expects($this->once())->method('addMessage')->with($error)->willReturnSelf();
        $errorMessages->expects($this->once())->method('getCount')->willReturn(1);

        $messagesBlock = $this->createMock(Messages::class);
        $messagesBlock->expects($this->once())->method('setMessages')->with($errorMessages)->willReturnSelf();
        $messagesBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('<div class="messages"><div class="message error">error</div></div>');

        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(Messages::class)
            ->willReturn($messagesBlock);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messages);
        $this->messageCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($errorMessages);
        $this->layoutFactory->expects($this->once())->method('create')->willReturn($layout);

        $this->assertSame(
            ['html' => '<div class="messages" role="alert" aria-atomic="true">'
                . '<div class="message error">error</div></div>'],
            $this->model->getInlineErrorMessages(true)
        );
    }

    /**
     * @return void
     */
    public function testGetInlineErrorMessagesRendersNotices(): void
    {
        $notice = new Notice();
        $notice->setText('Some options for this product were not found.');

        $messages = $this->createMock(Collection::class);
        $messages->expects($this->exactly(2))
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, []],
                [MessageInterface::TYPE_NOTICE, [$notice]],
            ]);

        $relevantMessages = $this->createMock(Collection::class);
        $relevantMessages->expects($this->once())->method('addMessage')->with($notice)->willReturnSelf();
        $relevantMessages->expects($this->once())->method('getCount')->willReturn(1);

        $messagesBlock = $this->createMock(Messages::class);
        $messagesBlock->expects($this->once())->method('setMessages')->with($relevantMessages)->willReturnSelf();
        $messagesBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('<div class="messages"><div class="message notice">notice</div></div>');

        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(Messages::class)
            ->willReturn($messagesBlock);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messages);
        $this->messageCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($relevantMessages);
        $this->layoutFactory->expects($this->once())->method('create')->willReturn($layout);

        $this->assertSame(
            ['html' => '<div class="messages" role="alert" aria-atomic="true">'
                . '<div class="message notice">notice</div></div>'],
            $this->model->getInlineErrorMessages(true)
        );
    }

    /**
     * @return void
     */
    public function testGetInlineErrorMessagesPreservesExtraAttributesAndClasses(): void
    {
        $error = new Error();
        $error->setText('Product that you are trying to add is not available.');

        $messages = $this->createMock(Collection::class);
        $messages->expects($this->exactly(2))
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, [$error]],
                [MessageInterface::TYPE_NOTICE, []],
            ]);

        $errorMessages = $this->createMock(Collection::class);
        $errorMessages->expects($this->once())->method('addMessage')->with($error)->willReturnSelf();
        $errorMessages->expects($this->once())->method('getCount')->willReturn(1);

        $messagesBlock = $this->createMock(Messages::class);
        $messagesBlock->expects($this->once())->method('setMessages')->with($errorMessages)->willReturnSelf();
        $messagesBlock->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn(
                '<div data-bind="scope: \'messages\'" class="messages extra-class">'
                . '<div class="message error">error</div></div>'
            );

        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(Messages::class)
            ->willReturn($messagesBlock);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($messages);
        $this->messageCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($errorMessages);
        $this->layoutFactory->expects($this->once())->method('create')->willReturn($layout);

        $result = $this->model->getInlineErrorMessages(true);

        $this->assertStringContainsString('data-bind="scope: \'messages\'"', $result['html']);
        $this->assertStringContainsString('class="messages extra-class"', $result['html']);
        $this->assertStringContainsString('role="alert"', $result['html']);
        $this->assertStringContainsString('aria-atomic="true"', $result['html']);
    }
}
