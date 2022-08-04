<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdminAdobeIms\Model\ImsConnection;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdminAdobeIms\Service\ImsOrganizationService;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthenticationException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdobeImsAdminTokenUserService
{
    private const ADOBE_IMS_MODULE_NAME = 'adobe_ims_auth';

    /**
     * @var ImsConfig
     */
    private ImsConfig $adminImsConfig;

    /**
     * @var ImsConnection
     */
    private ImsConnection $adminImsConnection;

    /**
     * @var ImsOrganizationService
     */
    private ImsOrganizationService $adminOrganizationService;

    /**
     * @var AdminLoginProcessService
     */
    private AdminLoginProcessService $adminLoginProcessService;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @param ImsConfig $adminImsConfig
     * @param ImsConnection $adminImsConnection
     * @param ImsOrganizationService $adminOrganizationService
     * @param AdminLoginProcessService $adminLoginProcessService
     * @param RequestInterface $request
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        ImsConnection $adminImsConnection,
        ImsOrganizationService $adminOrganizationService,
        AdminLoginProcessService $adminLoginProcessService,
        RequestInterface $request
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->adminImsConnection = $adminImsConnection;
        $this->adminOrganizationService = $adminOrganizationService;
        $this->adminLoginProcessService = $adminLoginProcessService;
        $this->request = $request;
    }

    /**
     * Process login request to Admin Adobe IMS.
     *
     * @return void
     * @throws AuthenticationException
     * @throws AdobeImsAuthorizationException
     */
    public function processLoginRequest()
    {
        if ($this->adminImsConfig->enabled() && $this->request->getParam('code')
            && $this->request->getModuleName() === self::ADOBE_IMS_MODULE_NAME) {
            try {
                $code = $this->request->getParam('code');

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
                throw new AdobeImsAuthorizationException(
                    __('You don\'t have access to this Commerce instance')
                );
            } catch (AdobeImsOrganizationAuthorizationException $e) {
                throw new AdobeImsOrganizationAuthorizationException(
                    __('Unable to sign in with the Adobe ID')
                );
            }
        } else {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }
    }
}
