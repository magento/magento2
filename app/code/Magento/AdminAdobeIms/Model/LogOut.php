<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\GetProfileInterface;
use Magento\AdminAdobeIms\Api\ImsLogOutInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * Represent functionality for log out users from the Adobe account
 */
class LogOut implements ImsLogOutInterface
{
    /**
     * Successful result code.
     */
    private const HTTP_OK = 200;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $imsConfig;

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
     * @param ConfigInterface $imsConfig
     * @param CurlFactory $curlFactory
     * @param GetProfileInterface $profile
     * @param Auth $auth
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $imsConfig,
        CurlFactory $curlFactory,
        GetProfileInterface $profile,
        Auth $auth
    ) {
        $this->logger = $logger;
        $this->curlFactory = $curlFactory;
        $this->imsConfig = $imsConfig;
        $this->profile = $profile;
        $this->auth = $auth;
    }

    /**
     * @inheritDoc
     */
    public function execute(?string $accessToken = null): bool
    {
        try {
            if ($accessToken === null) {
                $session = $this->auth->getAuthStorage();
                $accessToken = $session->getAdobeAccessToken();
            }

            if (empty($accessToken)) {
                return true;
            }

            $this->externalLogOut($accessToken);
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
        if (!$this->checkUserProfile($accessToken)) {
            throw new LocalizedException(
                __('An error occurred during logout operation.')
            );
        }
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');

        $curl->post(
            $this->imsConfig->getBackendLogoutUrl($accessToken),
            []
        );

        if ($curl->getStatus() !== self::HTTP_OK || ($this->checkUserProfile($accessToken))) {
            throw new LocalizedException(
                __('An error occurred during logout operation.')
            );
        }
    }

    /**
     * Checks whether user profile could be got by the access token
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
