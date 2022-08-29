<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;

use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;

class ImsCallback extends Auth implements HttpGetActionInterface
{
    public const ACTION_NAME = 'imscallback';

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * @var UserContextInterface
     */
    private UserContextInterface $userContext;

    /**
     * @param Context $context
     * @param ImsConfig $adminImsConfig
     * @param AdminAdobeImsLogger $logger
     * @param UserContextInterface $userContext
     */
    public function __construct(
        Context $context,
        ImsConfig $adminImsConfig,
        AdminAdobeImsLogger $logger,
        UserContextInterface $userContext
    ) {
        parent::__construct($context);
        $this->adminImsConfig = $adminImsConfig;
        $this->logger = $logger;
        $this->userContext = $userContext;
    }

    /**
     * Execute AdobeIMS callback
     *
     * @return Redirect
     */
    public function execute(): Redirect
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->_helper->getHomePageUrl());

        if (!$this->adminImsConfig->enabled()) {
            $this->getMessageManager()->addErrorMessage('Adobe Sign-In is disabled.');
            return $resultRedirect;
        }

        try {
            if ($this->userContext->getUserId()
                && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_ADMIN
            ) {
                return $resultRedirect;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            $this->imsErrorMessage(
                'Error signing in',
                'Something went wrong and we could not sign you in. ' .
                'Please try again or contact your administrator.'
            );
        }

        return $resultRedirect;
    }

    /**
     * Add AdminAdobeIMS Error Message
     *
     * @param string $headline
     * @param string $message
     * @return void
     */
    private function imsErrorMessage(string $headline, string $message): void
    {
        $this->messageManager->addComplexErrorMessage(
            'adminAdobeImsMessage',
            [
                'headline' => __($headline)->getText(),
                'message' => __($message)->getText()
            ]
        );
    }
}
