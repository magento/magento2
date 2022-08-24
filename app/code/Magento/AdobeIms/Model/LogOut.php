<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\AdobeImsApi\Api\LogOutInterface;
use Magento\Backend\Model\Auth;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * Represent functionality for log out users from the Adobe account
 */
class LogOut implements LogOutInterface
{
    /**
     * Successful result code.
     */
    private const HTTP_OK = 200;

    /**
     * Successful result code.
     */
    private const HTTP_FOUND = 302;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var GetAccessTokenInterface
     */
    private $getAccessToken;

    /**
     * @var FlushUserTokensInterface
     */
    private $flushUserTokens;

    /**
     * @var GetProfileInterface
     */
    private GetProfileInterface $profile;

    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param CurlFactory $curlFactory
     * @param GetAccessTokenInterface $getAccessToken
     * @param FlushUserTokensInterface $flushUserTokens
     * @param GetProfileInterface $profile
     * @param Auth $auth
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        CurlFactory $curlFactory,
        GetAccessTokenInterface $getAccessToken,
        FlushUserTokensInterface $flushUserTokens,
        GetProfileInterface $profile,
        Auth $auth
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->curlFactory = $curlFactory;
        $this->getAccessToken = $getAccessToken;
        $this->flushUserTokens = $flushUserTokens;
        $this->profile = $profile;
        $this->auth = $auth;
    }

    /**
     * @inheritDoc
     */
    public function execute(?string $accessToken = null) : bool
    {
        try {
            if ($accessToken === null) {
                $session = $this->auth->getAuthStorage();
                $accessToken = $session->getAdobeAccessToken();
            }
            if (!empty($accessToken)) {
                return $this->logoutAdminFromIms($accessToken);
            }
            $accessToken = $accessToken ?? $this->getAccessToken->execute();
            if (empty($accessToken)) {
                return true;
            }
            $this->externalLogOut($accessToken);
            $this->flushUserTokens->execute();
            return true;
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            return false;
        }
    }

    /**
     * Logout user from Adobe IMS
     *
     * @param string $accessToken
     * @throws LocalizedException
     */
    private function externalLogOut(string $accessToken): void
    {
        $curl = $this->curlFactory->create();
        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');
        $curl->get($this->config->getLogoutUrl($accessToken));

        if ($curl->getStatus() !== self::HTTP_FOUND) {
            throw new LocalizedException(
                __('An error occurred during logout operation.')
            );
        }
    }

    /**
     * Logout admin from Adobe IMS
     *
     * @param string $accessToken
     * @return bool
     * @throws LocalizedException
     */
    private function logoutAdminFromIms(string $accessToken): bool
    {
        if (!$this->checkUserProfile($accessToken)) {
            throw new LocalizedException(
                __('An error occurred during logout operation.')
            );
        }
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');

        $curl->post(
            $this->config->getBackendLogoutUrl($accessToken),
            []
        );

        if ($curl->getStatus() !== self::HTTP_OK || ($this->checkUserProfile($accessToken))) {
            throw new LocalizedException(
                __('An error occurred during logout operation.')
            );
        }
        return true;
    }

    /**
     * Check whether user profile could be retrieved by the access token
     *  - If the token is invalidated, profile information won't be returned
     *
     * @param string $accessToken
     * @return bool
     */
    private function checkUserProfile(string $accessToken): bool
    {
        try {
            $profile = $this->profile->getProfile($accessToken);
            if (!empty($profile['email'])) {
                return true;
            }
        } catch (AuthorizationException $exception) {
            return false;
        }
        return false;
    }
}
