<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\FlushUserTokensInterface;
use Magento\AdobeImsApi\Api\GetAccessTokenInterface;
use Magento\AdobeImsApi\Api\LogOutInterface;
use Magento\AdobeImsApi\Api\ConfigInterface;
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
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param CurlFactory $curlFactory
     * @param GetAccessTokenInterface $getAccessToken
     * @param FlushUserTokensInterface $flushUserTokens
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        CurlFactory $curlFactory,
        GetAccessTokenInterface $getAccessToken,
        FlushUserTokensInterface $flushUserTokens
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->curlFactory = $curlFactory;
        $this->getAccessToken = $getAccessToken;
        $this->flushUserTokens = $flushUserTokens;
    }

    /**
     * @inheritDoc
     */
    public function execute() : bool
    {
        try {
            $accessToken = $this->getAccessToken->execute();

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
}
