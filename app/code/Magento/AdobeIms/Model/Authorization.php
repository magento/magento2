<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdobeIms\Model;

use Laminas\Uri\Uri;
use Magento\AdobeImsApi\Api\AuthorizationInterface;
use Magento\AdobeImsApi\Api\ConfigInterface;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Stdlib\Parameters;

/**
 * Provide auth url and validate authorization
 */
class Authorization implements AuthorizationInterface
{
    private const HTTP_REDIRECT_CODE = 302;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $imsConfig;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;

    /**
     * @var string|null
     */
    private $redirecrHost = null;

    /**
     * @var Parameters
     */
    private Parameters $parameters;

    /**
     * @var Uri
     */
    private Uri $uri;

    /**
     * @param CurlFactory $curlFactory
     * @param ConfigInterface $imsConfig
     * @param Parameters $parameters
     * @param Uri $uri
     */
    public function __construct(
        CurlFactory $curlFactory,
        ConfigInterface $imsConfig,
        Parameters $parameters,
        Uri $uri
    ) {
        $this->curlFactory = $curlFactory;
        $this->imsConfig = $imsConfig;
        $this->parameters = $parameters;
        $this->uri = $uri;
    }

    /**
     * Get authorization url
     *
     * @param string|null $clientId
     * @return string
     * @throws InvalidArgumentException
     */
    public function getAuthUrl(?string $clientId = null): string
    {
        $authUrl = $this->imsConfig->getAdminAdobeImsAuthUrl($clientId);
        $imsUrl = $this->getAuthorizationLocation($authUrl);
        $this->validateRedirectUrls($authUrl, $imsUrl);

        return $imsUrl;
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
        $location = $this->getAuthUrl($clientId);
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
        $curl->get($authUrl);

        $this->validateResponse($curl);

        return $curl->getHeaders()['location'] ?? '';
    }

    /**
     * Validate authorization call response
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
                $error
            )
                && isset($error[0], $error[1])
            ) {
                throw new InvalidArgumentException(
                    __('Could not connect to Adobe IMS Service: %1.', $error[1])
                );
            }
        }

        if ($curl->getStatus() !== self::HTTP_REDIRECT_CODE) {
            throw new InvalidArgumentException(
                __('Could not get a valid response from Adobe IMS Service.')
            );
        }
    }

    /**
     * Validate current host and IMS returned host to make sure credentials belongs to correct project.
     *
     * @param string $authUrl
     * @param string $imsUrl
     * @throws InvalidArgumentException
     */
    private function validateRedirectUrls(string $authUrl, string $imsUrl)
    {
        $imsRedirectUrlHost = $this->getRedirectUrlHost($imsUrl);
        $currentRedirectHost = $this->getRedirectUrlHost($authUrl);
        if (!($imsRedirectUrlHost && $currentRedirectHost) || !($imsRedirectUrlHost === $currentRedirectHost)) {
            throw new InvalidArgumentException(
                __('Could not get a valid response from Adobe IMS Service.')
            );
        }
    }

    /**
     * Get host from redirect Url
     *
     * @param string $imsUrl
     * @return string|null
     */
    private function getRedirectUrlHost(string $imsUrl): ?string
    {
        $this->uri->parse($imsUrl);
        $this->parameters->fromString($this->uri->getQuery());
        $urlParams = $this->parameters->toArray();
        if (!isset($urlParams['redirect_uri'])) {
            foreach ($urlParams as $param => $value) {
                if ($param === 'callback' || $param === 'uc_callback') {
                    $this->getRedirectUrlHost($value);
                } elseif ($this->redirecrHost) {
                    break;
                }
            }
        } elseif (isset($urlParams['redirect_uri'])) {
            $this->uri->parse($urlParams['redirect_uri']);
            $this->redirecrHost = $this->uri->getHost();
        }
        return $this->redirecrHost;
    }
}
