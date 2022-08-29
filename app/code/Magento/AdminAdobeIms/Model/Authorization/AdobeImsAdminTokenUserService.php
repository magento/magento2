<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\AdobeImsApi\Api\OrganizationMembershipInterface;
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
     * @var GetTokenInterface
     */
    private GetTokenInterface $token;

    /**
     * @var GetProfileInterface
     */
    private GetProfileInterface $profile;

    /**
     * @var OrganizationMembershipInterface
     */
    private OrganizationMembershipInterface $organizationMembership;

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
     * @param OrganizationMembershipInterface $organizationMembership
     * @param AdminLoginProcessService $adminLoginProcessService
     * @param RequestInterface $request
     * @param GetTokenInterface $token
     * @param GetProfileInterface $profile
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        OrganizationMembershipInterface $organizationMembership,
        AdminLoginProcessService $adminLoginProcessService,
        RequestInterface $request,
        GetTokenInterface $token,
        GetProfileInterface $profile
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->organizationMembership = $organizationMembership;
        $this->adminLoginProcessService = $adminLoginProcessService;
        $this->request = $request;
        $this->token = $token;
        $this->profile = $profile;
    }

    /**
     * Process login request to Admin Adobe IMS.
     *
     * @return void
     * @throws AdobeImsAuthorizationException
     * @throws AdobeImsOrganizationAuthorizationException
     * @throws AuthenticationException
     */
    public function processLoginRequest()
    {
        if ($this->adminImsConfig->enabled() && $this->request->getParam('code')
            && $this->request->getModuleName() === self::ADOBE_IMS_MODULE_NAME) {
            try {
                $code = $this->request->getParam('code');

                //get token from response
                $tokenResponse = $this->token->getTokenResponse($code);
                $accessToken = $tokenResponse->getAccessToken();

                //get profile info to check email
                $profile = $this->profile->getProfile($accessToken);
                if (empty($profile['email'])) {
                    throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
                }

                //check membership in organization
                $this->organizationMembership->checkOrganizationMembership($accessToken);

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
