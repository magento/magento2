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
use Magento\AdobeImsApi\Api\OrganizationMembershipInterface;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\Auth;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\AdobeImsApi\Api\GetTokenInterface;
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
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var OrganizationMembershipInterface
     */
    private OrganizationMembershipInterface $organizationMembership;

    /**
     * @var AdminReauthProcessService
     */
    private AdminReauthProcessService $adminReauthProcessService;

    /**
     * @var AdminAdobeImsLogger
     */
    private AdminAdobeImsLogger $logger;

    /**
     * @var GetTokenInterface
     */
    private GetTokenInterface $token;

    /**
     * @var GetProfileInterface
     */
    private GetProfileInterface $profile;

    /**
     * @param Context $context
     * @param GetProfileInterface $profile
     * @param ImsConfig $adminImsConfig
     * @param OrganizationMembershipInterface $organizationMembership
     * @param AdminReauthProcessService $adminReauthProcessService
     * @param AdminAdobeImsLogger $logger
     * @param GetTokenInterface $token
     */
    public function __construct(
        Context                         $context,
        GetProfileInterface             $profile,
        ImsConfig                       $adminImsConfig,
        OrganizationMembershipInterface $organizationMembership,
        AdminReauthProcessService       $adminReauthProcessService,
        AdminAdobeImsLogger             $logger,
        GetTokenInterface               $token
    ) {
        parent::__construct($context);
        $this->profile = $profile;
        $this->adminImsConfig = $adminImsConfig;
        $this->organizationMembership = $organizationMembership;
        $this->adminReauthProcessService = $adminReauthProcessService;
        $this->logger = $logger;
        $this->token = $token;
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

            $tokenResponse = $this->token->getTokenResponse($code);
            $accessToken = $tokenResponse->getAccessToken();

            $profile = $this->profile->getProfile($accessToken);
            if (empty($profile['email'])) {
                throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
            }

            //check membership in organization
            $this->organizationMembership->checkOrganizationMembership($accessToken);

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
