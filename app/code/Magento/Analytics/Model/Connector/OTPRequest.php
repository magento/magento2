<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\Connector\Http\ResponseResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Representation of an 'OTP' request.
 *
 * The request is responsible for obtaining of an OTP from the MBI service.
 *
 * OTP (One-Time Password) is a password that is valid for short period of time
 * and may be used only for one login session.
 * @since 2.2.0
 */
class OTPRequest
{
    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * @var Http\ClientInterface
     * @since 2.2.0
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     * @since 2.2.0
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $config;

    /**
     * @var ResponseResolver
     * @since 2.2.0
     */
    private $responseResolver;

    /**
     * Path to the configuration value which contains
     * an URL that provides an OTP.
     *
     * @var string
     * @since 2.2.0
     */
    private $otpUrlConfigPath = 'analytics/url/otp';

    /**
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param ResponseResolver $responseResolver
     * @param LoggerInterface $logger
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        ScopeConfigInterface $config,
        ResponseResolver $responseResolver,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->responseResolver = $responseResolver;
        $this->logger = $logger;
    }

    /**
     * Performs obtaining of an OTP from the MBI service.
     *
     * Returns received OTP or FALSE in case of failure.
     *
     * @return string|false
     * @since 2.2.0
     */
    public function call()
    {
        $result = false;

        if ($this->analyticsToken->isTokenExist()) {
            $response = $this->httpClient->request(
                ZendClient::POST,
                $this->config->getValue($this->otpUrlConfigPath),
                [
                    "access-token" => $this->analyticsToken->getToken(),
                    "url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
                ]
            );

            $result = $this->responseResolver->getResult($response);
            if (!$result) {
                $this->logger->warning(
                    sprintf(
                        'Obtaining of an OTP from the MBI service has been failed: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                    )
                );
            }
        }

        return $result;
    }
}
