<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\AdminAdobeIms\Service\ImsConfig;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;

class ImsConnection
{
    private const HTTP_REDIRECT_CODE = 302;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var ImsConfig
     */
    private ImsConfig $imsConfig;

    /**
     * @param CurlFactory $curlFactory
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        CurlFactory $curlFactory,
        ImsConfig $imsConfig
    ) {
        $this->curlFactory = $curlFactory;
        $this->imsConfig = $imsConfig;
    }

    /**
     * Get authorization url
     *
     * @param string|null $clientId
     * @return string
     * @throws InvalidArgumentException
     */
    public function auth(?string $clientId = null): string
    {
        $authUrl = $this->imsConfig->getAdminAdobeImsAuthUrl($clientId);
        return $this->getAuthorizationLocation($authUrl);
    }

    /**
     * Test if given ClientID is valid and is able to return an authorization URL
     *
     * @param string $clientId
     * @return bool
     * @throws InvalidArgumentException
     */
    public function testAuth(string $clientId): bool
    {
        $location = $this->auth($clientId);
        return $location !== '';
    }

    /**
     * Get authorization location from adobeIMS
     *
     * @param string $authUrl
     * @return string
     * @throws InvalidArgumentException
     */
    private function getAuthorizationLocation(string $authUrl): string
    {
        $curl = $this->curlFactory->create();

        $curl->addHeader('Content-Type', 'application/x-www-form-urlencoded');
        $curl->addHeader('cache-control', 'no-cache');
        $curl->post($authUrl, []);

        $this->validateResponse($curl);

        return $curl->getHeaders()['location'] ?? '';
    }

    /**
     * validate authorization call response
     *
     * @param Curl $curl
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateResponse(Curl $curl): void
    {
        if (isset($curl->getHeaders()['location'])) {
            if (preg_match(
                '/error=([a-z_]+)/i',
                $curl->getHeaders()['location'],
                $error)
            ) {
                if (isset($error[0], $error[1])) {
                    throw new InvalidArgumentException(
                        __('Could not connect to Adobe IMS Service: %1.', $error[1])
                    );
                }
            }
        }

        if ($curl->getStatus() !== self::HTTP_REDIRECT_CODE) {
            throw new InvalidArgumentException(__('Could not connect to Adobe IMS Service.'));
        }
    }
}
