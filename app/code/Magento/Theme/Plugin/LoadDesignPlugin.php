<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\DesignLoader;

/**
 * Handling Exceptions on Design Loading
 */
class LoadDesignPlugin
{
    /**
     * @var DesignLoader
     */
    private $designLoader;

    /**
     * @var MessageManagerInterface
     */
    private $messageManager;

    /**
     * @param DesignLoader $designLoader
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        DesignLoader $designLoader,
        MessageManagerInterface $messageManager
    ) {
        $this->designLoader = $designLoader;
        $this->messageManager = $messageManager;
    }

    /**
     * Initialize design
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        try {
            $this->designLoader->load();
        } catch (LocalizedException $e) {
            if ($e->getPrevious() instanceof ValidationException) {
                /** @var MessageInterface $message */
                $message = $this->messageManager
                    ->createMessage(MessageInterface::TYPE_ERROR)
                    ->setText($e->getMessage());
                $this->messageManager->addUniqueMessages([$message]);
            }
        }
    }
}
