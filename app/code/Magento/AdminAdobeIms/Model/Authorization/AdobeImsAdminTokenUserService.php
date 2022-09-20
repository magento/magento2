<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model\Authorization;

use Magento\AdminAdobeIms\Exception\AdobeImsAuthorizationException;
use Magento\AdminAdobeIms\Service\AdminLoginProcessService;
use Magento\AdminAdobeIms\Service\AdminReauthProcessService;
use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\AdobeIms\Exception\AdobeImsOrganizationAuthorizationException;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterface;
use Magento\AdobeImsApi\Api\Data\TokenResponseInterfaceFactory;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\AdobeImsApi\Api\GetTokenInterface;
use Magento\AdobeImsApi\Api\OrganizationMembershipInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;

/**
 * Adobe IMS Auth Model for getting Admin Token
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdobeImsAdminTokenUserService
{
    private const ADOBE_IMS_MODULE_NAME = 'adobe_ims_auth';
    private const AUTHORIZATION_METHOD_HEADER_BEARER = 'bearer';

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
     * @var AdminReauthProcessService
     */
    private AdminReauthProcessService $adminReauthProcessService;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var TokenResponseInterfaceFactory
     */
    private TokenResponseInterfaceFactory $tokenResponseFactory;

    /**
     * @param ImsConfig $adminImsConfig
     * @param OrganizationMembershipInterface $organizationMembership
     * @param AdminLoginProcessService $adminLoginProcessService
     * @param AdminReauthProcessService $adminReauthProcessService
     * @param RequestInterface $request
     * @param GetTokenInterface $token
     * @param GetProfileInterface $profile
     * @param TokenResponseInterfaceFactory $tokenResponseFactory
     */
    public function __construct(
        ImsConfig $adminImsConfig,
        OrganizationMembershipInterface $organizationMembership,
        AdminLoginProcessService $adminLoginProcessService,
        AdminReauthProcessService $adminReauthProcessService,
        RequestInterface $request,
        GetTokenInterface $token,
        GetProfileInterface $profile,
        TokenResponseInterfaceFactory $tokenResponseFactory
    ) {
        $this->adminImsConfig = $adminImsConfig;
        $this->organizationMembership = $organizationMembership;
        $this->adminLoginProcessService = $adminLoginProcessService;
        $this->adminReauthProcessService = $adminReauthProcessService;
        $this->request = $request;
        $this->token = $token;
        $this->profile = $profile;
        $this->tokenResponseFactory = $tokenResponseFactory;
    }

    /**
     * Process login request to Admin Adobe IMS.
     *
     * @param bool $isReauthorize
     * @return void
     * @throws AdobeImsAuthorizationException
     * @throws AdobeImsOrganizationAuthorizationException
     * @throws AuthenticationException|AuthorizationException
     */
    public function processLoginRequest(bool $isReauthorize = false): void
    {
        if ($this->adminImsConfig->enabled()
            && $this->request->getModuleName() === self::ADOBE_IMS_MODULE_NAME) {
            try {
                if ($this->request->getHeader('Authorization')) {
                    $tokenResponse = $this->getRequestedToken();
                } elseif ($this->request->getParam('code')) {
                    $code = $this->request->getParam('code');
                    $tokenResponse = $this->token->getTokenResponse($code);
                } else {
                    throw new AuthenticationException(__('Unable to get Access Token. Please try again.'));
                }

                $this->getLoggedIn($isReauthorize, $tokenResponse);
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

    /**
     * Get requested token using Authorization header
     *
     * @return TokenResponseInterface
     * @throws AuthenticationException
     */
    private function getRequestedToken(): TokenResponseInterface
    {
        $authorizationHeaderValue = $this->request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }

        $headerPieces = explode(" ", $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }

        $tokenType = strtolower($headerPieces[0]);
        if ($tokenType !== self::AUTHORIZATION_METHOD_HEADER_BEARER) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }

        $tokenResponse['access_token'] = $headerPieces[1];
        return $this->tokenResponseFactory->create(['data' => $tokenResponse]);
    }

    /**
     * Responsible for logging in to Admin Panel
     *
     * @param bool $isReauthorize
     * @param TokenResponseInterface $tokenResponse
     * @return void
     * @throws AdobeImsAuthorizationException
     * @throws AuthenticationException
     * @throws AuthorizationException
     */
    private function getLoggedIn(bool $isReauthorize, TokenResponseInterface $tokenResponse): void
    {
        $profile = $this->profile->getProfile($tokenResponse->getAccessToken());
        if (empty($profile['email'])) {
            throw new AuthenticationException(__('An authentication error occurred. Verify and try again.'));
        }

        $this->organizationMembership->checkOrganizationMembership($tokenResponse->getAccessToken());

        if ($isReauthorize) {
            $this->adminReauthProcessService->execute($tokenResponse);
        } else {
            $this->adminLoginProcessService->execute($tokenResponse, $profile);
        }
    }
}
