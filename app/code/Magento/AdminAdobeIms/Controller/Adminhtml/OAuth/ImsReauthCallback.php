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
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\AuthenticationException;

class ImsReauthCallback extends Auth implements HttpGetActionInterface
{
    public const ACTION_NAME = 'imsreauthcallback';

    /**
     * Constants of response
     *
     * RESPONSE_TEMPLATE - template of response
     * RESPONSE_SUCCESS_CODE success code
     * RESPONSE_ERROR_CODE error code
     */
    private const RESPONSE_TEMPLATE = 'auth[code=%s;message=%s]';
    private const RESPONSE_SUCCESS_CODE = 'success';
    private const RESPONSE_ERROR_CODE = 'error';

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
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        /** @var Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        if (!$this->imsConfig->enabled()) {
            $this->getMessageManager()->addErrorMessage('Adobe Sign-In is disabled.');

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                __('Adobe Sign-In is disabled.')
            );

            $resultRaw->setContents($response);

            return $resultRaw;
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
            $this->adminLoginProcessService->reAuth($profile, $tokenResponse);

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_SUCCESS_CODE,
                __('Authorization was successful')
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());

            $response = sprintf(
                self::RESPONSE_TEMPLATE,
                self::RESPONSE_ERROR_CODE,
                $e->getMessage()
            );
        }

        $resultRaw->setContents($response);

        return $resultRaw;
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
