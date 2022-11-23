<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\AdobeImsApi\Api\IsTokenValidInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class IsTokenValid implements IsTokenValidInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CurlFactory $curlFactory
     * @param ConfigInterface $config
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlFactory $curlFactory,
        ConfigInterface $config,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->config = $config;
        $this->json = $json;
        $this->logger = $logger;
    }

    /**
     * Validate token
     *
     * @param string|null $token
     * @param string $tokenType
     * @return bool
     * @throws AuthorizationException
     */
    public function validateToken(?string $token, string $tokenType = 'access_token'): bool
    {
        $isTokenValid = false;

        if ($token === null) {
            return false;
        }

        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');

        $curl->post(
            $this->config->getValidateTokenUrl($token, $tokenType),
            []
        );

        if ($curl->getBody() === '') {
            throw new AuthorizationException(
                __('Could not verify the access_token')
            );
        }

        $body = $this->json->unserialize($curl->getBody());

        if (isset($body['valid'])) {
            $isTokenValid = (bool)$body['valid'];
        }

        if (!$isTokenValid && isset($body['reason'])) {
            $this->logger->info($tokenType . ' is not valid. Reason: ' . $body['reason']);
        }

        return $isTokenValid;
    }
}
