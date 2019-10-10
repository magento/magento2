<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin;

use Magento\Catalog\Model\Product;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\DesignLoader as ViewDesignLoader;
use Magento\Framework\Message\ManagerInterface;
use Magento\Catalog\Block\Product\ImageFactory;

/**
 * Load necessary design files for GraphQL
 */
class DesignLoader
{
    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ViewDesignLoader $designLoader
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ViewDesignLoader $designLoader,
        ManagerInterface $messageManager
    ) {
        $this->designLoader = $designLoader;
        $this->messageManager = $messageManager;
    }

    /**
     * Before create load the design files
     *
     * @param ImageFactory $subject
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCreate(
        ImageFactory $subject,
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
