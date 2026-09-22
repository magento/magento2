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
        $errorMessages = $this->getRelevantMessages($messages);
        if (!$errorMessages->getCount()) {
            return null;
        }

        $block = $this->layoutFactory->create()->createBlock(Messages::class);
        $block->setMessages($errorMessages);

        return [
            'html' => $this->addAlertAttributes($block->getGroupedHtml()),
        ];
    }

    /**
     * Add the role="alert" wrapper the storefront Knockout messages component renders
     *
     * @param string $html
     * @return string
     */
    private function addAlertAttributes(string $html): string
    {
        if ($html === '') {
            return $html;
        }

        $document = new \DOMDocument();
        $useInternalErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        $wrapper = $document->documentElement;
        if (!$wrapper instanceof \DOMElement) {
            return $html;
        }

        $wrapper->setAttribute('role', 'alert');
        $wrapper->setAttribute('aria-atomic', 'true');

        return $document->saveHTML($wrapper);
    }

    /**
     * Extract error/notice messages from the message collection.
     *
     * @param Collection $messages
     * @return Collection
     */
    private function getRelevantMessages(Collection $messages): Collection
    {
        $relevantMessages = $this->messageCollectionFactory->create();
        foreach ($messages->getItemsByType(MessageInterface::TYPE_ERROR) as $message) {
            $relevantMessages->addMessage($message);
        }
        foreach ($messages->getItemsByType(MessageInterface::TYPE_NOTICE) as $message) {
            $relevantMessages->addMessage($message);
        }

        return $relevantMessages;
    }
}
