<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Controller\Adminhtml\OAuth;

use Exception;
use Magento\AdminAdobeIms\Logger\AdminAdobeImsLogger;
use Magento\AdminAdobeIms\Service\AdminReauthProcessService;
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
     * @var AdminReauthProcessService
     */
    private AdminReauthProcessService $adminReauthProcessService;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * @param Context $context
     * @param ImsConnection $adminImsConnection
     * @param ImsConfig $adminImsConfig
     * @param ImsOrganizationService $adminOrganizationService
     * @param AdminReauthProcessService $adminReauthProcessService
     * @param AdminAdobeImsLogger $logger
     */
    public function __construct(
        Context $context,
        ImsConnection $adminImsConnection,
        ImsConfig $adminImsConfig,
        ImsOrganizationService $adminOrganizationService,
        AdminReauthProcessService $adminReauthProcessService,
        AdminAdobeImsLogger $logger
    ) {
        parent::__construct($context);
        $this->adminImsConnection = $adminImsConnection;
        $this->adminImsConfig = $adminImsConfig;
        $this->adminOrganizationService = $adminOrganizationService;
        $this->adminReauthProcessService = $adminReauthProcessService;
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

        if (!$this->adminImsConfig->enabled()) {
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

            $tokenResponse = $this->adminImsConnection->getTokenResponse($code);
            $accessToken = $tokenResponse->getAccessToken();

            $profile = $this->adminImsConnection->getProfile($accessToken);
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            //check membership in organization
            $this->adminOrganizationService->checkOrganizationMembership($accessToken);

            $this->adminReauthProcessService->execute($tokenResponse);

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
}
