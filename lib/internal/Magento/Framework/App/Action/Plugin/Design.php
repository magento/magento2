<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action\Plugin;

use Magento\Framework\Message\MessageInterface;

/**
 * Class \Magento\Framework\App\Action\Plugin\Design
 *
 */
class Design
{
    /**
     * @var \Magento\Framework\View\DesignLoader
     */
    protected $_designLoader;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.1.0
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
        $this->_designLoader = $designLoader;
        $this->messageManager = $messageManager;
    }

    /**
     * Initialize design
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function beforeDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        try {
            $this->_designLoader->load();
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
