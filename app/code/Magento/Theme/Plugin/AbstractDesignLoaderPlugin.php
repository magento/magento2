<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Theme\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\DesignLoader;

/**
 * Loads a single part of the design before the Action is executed
 *
 * Handles Exceptions raised while the design configuration is being loaded.
 */
abstract class AbstractDesignLoaderPlugin
{
    /**
     * @var DesignLoader
     */
    protected $designLoader;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

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
     * Load the part of the design this plugin is responsible for
     *
     * @return void
     */
    abstract protected function loadPart(): void;

    /**
     * Initialize the part of the design this plugin is responsible for
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        try {
            $this->loadPart();
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
