<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
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
    private ImsConnection $adminImsConnection;

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var ImsOrganizationService
     */
    private ImsOrganizationService $adminOrganizationService;

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
     * @param ImsConnection $adminImsConnection
     * @param ImsConfig $adminImsConfig
     * @param ImsOrganizationService $adminOrganizationService
     * @param AdminLoginProcessService $adminLoginProcessService
     * @param AdminAdobeImsLogger $logger
     */
    public function __construct(
        Context $context,
        ImsConnection $adminImsConnection,
        ImsConfig $adminImsConfig,
        ImsOrganizationService $adminOrganizationService,
        AdminLoginProcessService $adminLoginProcessService,
        AdminAdobeImsLogger $logger
    ) {
        parent::__construct($context);
        $this->adminImsConnection = $adminImsConnection;
        $this->adminImsConfig = $adminImsConfig;
        $this->adminOrganizationService = $adminOrganizationService;
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

        if (!$this->adminImsConfig->enabled()) {
            $this->getMessageManager()->addErrorMessage('Adobe Sign-In is disabled.');
            return $resultRedirect;
        }

        try {
            $code = $this->getRequest()->getParam('code');

            if ($code === null) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            //get token from response
            $tokenResponse = $this->adminImsConnection->getTokenResponse($code);
            $accessToken = $tokenResponse->getAccessToken();

            //get profile info to check email
            $profile = $this->adminImsConnection->getProfile($accessToken);
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            //check membership in organization
            $this->adminOrganizationService->checkOrganizationMembership($accessToken);

            $this->adminLoginProcessService->execute($tokenResponse, $profile);
        } catch (AdobeImsAuthorizationException $e) {
            $this->logger->error($e->getMessage());

            $this->imsErrorMessage(
                'You don\'t have access to this Commerce instance',
                AdobeImsAuthorizationException::ERROR_MESSAGE
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
