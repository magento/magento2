<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model\Cart;

use Magento\Framework\Message\Collection;
use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;

/**
 * Prepares storefront error messages for AJAX add to cart responses.
 */
class AjaxMessageResponse
{
    /**
     * @param ManagerInterface $messageManager
     * @param LayoutFactory $layoutFactory
     * @param CollectionFactory $messageCollectionFactory
     */
    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly LayoutFactory $layoutFactory,
        private readonly CollectionFactory $messageCollectionFactory
    ) {
    }

    /**
     * Returns rendered error messages for inline AJAX display.
     *
     * @param bool $clearMessages
     * @return array{html: string}|null
     */
    public function getInlineErrorMessages(bool $clearMessages): ?array
    {
        $messages = $this->messageManager->getMessages($clearMessages);
        $errorMessages = $this->getErrorMessages($messages);
        if (!$errorMessages->getCount()) {
            return null;
        }

        $block = $this->layoutFactory->create()->createBlock(Messages::class);
        $block->setMessages($errorMessages);

        return [
            'html' => $block->getGroupedHtml(),
        ];
    }

    /**
     * Extract error messages from the message collection.
     *
     * @param Collection $messages
     * @return Collection
     */
    private function getErrorMessages(Collection $messages): Collection
    {
        $errorMessages = $this->messageCollectionFactory->create();
        foreach ($messages->getItemsByType(MessageInterface::TYPE_ERROR) as $message) {
            $errorMessages->addMessage($message);
        }

        return $errorMessages;
    }
}
