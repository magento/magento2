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
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Element\Messages;
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
    private CollectionFactory $collectionFactory;

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
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->model = $objectManager->getObject(
            AjaxMessageResponse::class,
            [
                'messageManager' => $this->messageManager,
                'layoutFactory' => $this->layoutFactory,
                'collectionFactory' => $this->collectionFactory,
            ]
        );
    }

    /**
     * @return void
     */
    public function testShouldNotDisplayInlineWithoutBackUrl(): void
    {
        $this->assertFalse($this->model->shouldDisplayInline(null, 'https://example.com/product.html'));
    }

    /**
     * @return void
     */
    public function testShouldDisplayInlineForSamePageRedirect(): void
    {
        $this->assertTrue(
            $this->model->shouldDisplayInline(
                'https://example.com/product.html',
                'https://example.com/product.html?foo=bar'
            )
        );
    }

    /**
     * @return void
     */
    public function testShouldNotDisplayInlineForCrossPageRedirect(): void
    {
        $this->assertFalse(
            $this->model->shouldDisplayInline(
                'https://example.com/product.html',
                'https://example.com/category.html'
            )
        );
    }

    /**
     * @return void
     */
    public function testResolveReturnsNullForSuccessMessagesOnly(): void
    {
        $sessionMessages = $this->createMock(Collection::class);
        $sessionMessages->expects($this->exactly(2))
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, []],
                [MessageInterface::TYPE_NOTICE, []],
            ]);

        $blockingMessages = $this->createMock(Collection::class);
        $blockingMessages->expects($this->once())->method('getCount')->willReturn(0);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(false)
            ->willReturn($sessionMessages);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($blockingMessages);

        $this->assertNull(
            $this->model->resolve(
                'https://example.com/product.html',
                'https://example.com/product.html'
            )
        );
    }

    /**
     * @return void
     */
    public function testResolveReturnsBlockingMessagesForSamePageError(): void
    {
        $errorMessage = $this->createMock(MessageInterface::class);
        $errorMessage->method('getType')->willReturn(MessageInterface::TYPE_ERROR);
        $errorMessage->method('getIdentifier')->willReturn('error');

        $sessionMessages = $this->createMock(Collection::class);
        $sessionMessages->expects($this->atLeastOnce())
            ->method('getItemsByType')
            ->willReturnMap([
                [MessageInterface::TYPE_ERROR, [$errorMessage]],
                [MessageInterface::TYPE_NOTICE, []],
            ]);
        $sessionMessages->expects($this->once())
            ->method('deleteMessageByIdentifier')
            ->with('error');

        $blockingMessages = $this->createMock(Collection::class);
        $blockingMessages->expects($this->once())->method('getCount')->willReturn(1);
        $blockingMessages->expects($this->once())
            ->method('addMessage')
            ->with($errorMessage)
            ->willReturnSelf();

        $messagesBlock = $this->createMock(Messages::class);
        $messagesBlock->expects($this->once())->method('setMessages')->with($blockingMessages)->willReturnSelf();
        $messagesBlock->expects($this->once())->method('getGroupedHtml')->willReturn('<div>error</div>');

        $layout = $this->createMock(Layout::class);
        $layout->expects($this->once())->method('getMessagesBlock')->willReturn($messagesBlock);

        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(false)
            ->willReturn($sessionMessages);
        $this->collectionFactory->expects($this->once())->method('create')->willReturn($blockingMessages);
        $this->layoutFactory->expects($this->once())->method('create')->willReturn($layout);

        $this->assertSame(
            [
                'html' => '<div>error</div>',
                'displayMessages' => true,
            ],
            $this->model->resolve(
                'https://example.com/product.html',
                'https://example.com/product.html'
            )
        );
    }
}
