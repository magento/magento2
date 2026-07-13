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
use Magento\Framework\View\LayoutFactory;

/**
 * Prepares storefront messages for AJAX add to cart responses.
 */
class AjaxMessageResponse
{
    /**
     * @param ManagerInterface $messageManager
     * @param LayoutFactory $layoutFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly ManagerInterface $messageManager,
        private readonly LayoutFactory $layoutFactory,
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * Whether messages should be rendered on the current page instead of relying on redirect.
     *
     * @param string|null $backUrl
     * @param string|null $refererUrl
     * @return bool
     */
    public function shouldDisplayInline(?string $backUrl, ?string $refererUrl): bool
    {
        if ($backUrl === null || $backUrl === '') {
            return false;
        }

        if ($refererUrl === null || $refererUrl === '') {
            return false;
        }

        return $this->normalizeUrl($backUrl) === $this->normalizeUrl($refererUrl);
    }

    /**
     * Returns rendered blocking messages for inline AJAX display.
     *
     * @param string|null $backUrl
     * @param string|null $refererUrl
     * @return array{html: string, displayMessages: bool}|null
     */
    public function resolve(?string $backUrl, ?string $refererUrl): ?array
    {
        if (!$this->shouldDisplayInline($backUrl, $refererUrl)) {
            return null;
        }

        $sessionMessages = $this->messageManager->getMessages(false);
        $blockingMessages = $this->createBlockingMessagesCollection($sessionMessages);
        if (!$blockingMessages->getCount()) {
            return null;
        }

        $block = $this->layoutFactory->create()->getMessagesBlock();
        $block->setMessages($blockingMessages);

        $this->clearBlockingMessages($sessionMessages);

        return [
            'html' => $block->getGroupedHtml(),
            'displayMessages' => true,
        ];
    }

    /**
     * Creates collection that contains only blocking storefront messages.
     *
     * @param Collection $source
     * @return Collection
     */
    private function createBlockingMessagesCollection(Collection $source): Collection
    {
        $collection = $this->collectionFactory->create();
        foreach ([MessageInterface::TYPE_ERROR, MessageInterface::TYPE_NOTICE] as $type) {
            foreach ($source->getItemsByType($type) as $message) {
                $collection->addMessage($message);
            }
        }

        return $collection;
    }

    /**
     * Removes blocking messages from session after inline rendering.
     *
     * @param Collection $messages
     * @return void
     */
    private function clearBlockingMessages(Collection $messages): void
    {
        foreach ([MessageInterface::TYPE_ERROR, MessageInterface::TYPE_NOTICE] as $type) {
            foreach ($messages->getItemsByType($type) as $message) {
                $messages->deleteMessageByIdentifier($message->getIdentifier());
            }
        }
    }

    /**
     * Normalizes a URL path for comparison by stripping query, fragment, and trailing slash.
     *
     * @param string $url
     * @return string
     */
    private function normalizeUrl(string $url): string
    {
        $normalizedUrl = explode('?', $url, 2)[0];
        $normalizedUrl = explode('#', $normalizedUrl, 2)[0];

        return rtrim($normalizedUrl, '/');
    }
}
