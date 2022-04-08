<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Exception\AdobeImsTokenAuthorizationException;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Service\ImsOrganizationService;
use Magento\Backend\App\Action\Context;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\AuthenticationException;

class ImsCallback extends Auth implements HttpGetActionInterface
{
    public const ACTION_NAME = 'imscallback';

    /**
     * @var ImsConnection
     */
    private ImsConnection $imsConnection;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @var ImsOrganizationService
     */
    private ImsOrganizationService $organizationService;

    /**
     * @var AdminLoginProcessService
     */
    private AdminLoginProcessService $adminLoginProcessService;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * @param Context $context
     * @param ImsConnection $imsConnection
     * @param ImsConfig $imsConfig
     * @param ImsOrganizationService $organizationService
     * @param AdminLoginProcessService $adminLoginProcessService
     * @param AdminAdobeImsLogger $logger
     */
    public function __construct(
        Context $context,
        ImsConnection $imsConnection,
        ImsConfig $imsConfig,
        ImsOrganizationService $organizationService,
        AdminLoginProcessService $adminLoginProcessService,
        AdminAdobeImsLogger $logger
    ) {
        parent::__construct($context);
        $this->imsConnection = $imsConnection;
        $this->imsConfig = $imsConfig;
        $this->organizationService = $organizationService;
        $this->adminLoginProcessService = $adminLoginProcessService;
        $this->logger = $logger;
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

        if (!$this->imsConfig->enabled()) {
            $this->getMessageManager()->addErrorMessage('Adobe Sign-In is disabled.');
            return $resultRedirect;
        }

        try {
            $code = $this->getRequest()->getParam('code');

            if ($code === null) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            $tokenResponse = $this->imsConnection->getTokenResponse($code);

            $profile = $this->imsConnection->getProfile($tokenResponse->getAccessToken());
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }
            $this->organizationService->checkOrganizationAllocation($profile);
            $this->adminLoginProcessService->execute($profile, $tokenResponse);
        } catch (AdobeImsWebapiAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            $this->imsErrorMessage(
                'You don\'t have access to this Commerce instance',
                AdobeImsWebapiAuthorizationException::ERROR_MESSAGE
            );
        } catch (AdobeImsOrganizationAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            $this->imsErrorMessage(
                'Unable to sign in with the Adobe ID',
                AdobeImsOrganizationAuthorizationException::ERROR_MESSAGE
            );
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
