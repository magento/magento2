<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\User\Controller\Adminhtml\Auth\Forgotpassword;

class AdminForgotPasswordPlugin
{
    /**
     * @var RedirectFactory
     */
    private RedirectFactory $redirectFactory;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var MessageManagerInterface
     */
    private MessageManagerInterface $messageManager;

    /**
     * @param RedirectFactory $redirectFactory
     * @param ImsConfig $imsConfig
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        RedirectFactory $redirectFactory,
        ImsConfig $imsConfig,
        MessageManagerInterface $messageManager
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->imsConfig = $imsConfig;
        $this->messageManager = $messageManager;
    }

    /**
     * Disable forgot password method when AdminAdobeIMS Module is enabled
     *
     * @param Forgotpassword $subject
     * @param callable $proceed
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(Forgotpassword $subject, callable $proceed)
    {
        if ($this->imsConfig->enabled()) {
            $resultRedirect = $this->redirectFactory->create();
            $this->messageManager->addErrorMessage(__('Please sign in with Adobe ID'));
            return $resultRedirect->setPath('admin');
        }

        return $proceed();
    }
}
