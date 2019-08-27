<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Message\MessageInterface;

/**
 * Load necessary design files for GraphQL
 */
class DesignLoader
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $designLoader;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\View\DesignLoader $designLoader
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\View\DesignLoader $designLoader,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->designLoader = $designLoader;
        $this->messageManager = $messageManager;
    }

    /**
     * Before create load the design files
     *
     * @param \Magento\Catalog\Block\Product\ImageFactory $subject
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreate(
        \Magento\Catalog\Block\Product\ImageFactory $subject,
        Product $product,
        string $imageId,
        array $attributes = null
    ) {
        try {
            $this->designLoader->load();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getPrevious() instanceof \Magento\Framework\Config\Dom\ValidationException) {
                /** @var MessageInterface $message */
                $message = $this->messageManager
                    ->createMessage(MessageInterface::TYPE_ERROR)
                    ->setText($e->getMessage());
                $this->messageManager->addUniqueMessages([$message]);
            }
        }
    }
}