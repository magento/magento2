<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Zend_Http_Response as HttpResponse;

/**
 * Representation of an 'OTP' request.
 *
 * The request is responsible for obtaining of an OTP from the MBI service.
 *
 * OTP (One-Time Password) is a password that is valid for short period of time
 * and may be used only for one login session.
 */
class OTPRequest
{
    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var Http\ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Path to the configuration value which contains
     * an URL that provides an OTP.
     *
     * @var string
     */
    private $otpUrlConfigPath = 'analytics/url/otp';

    /**
     * @param AnalyticsToken $analyticsToken
     * @param Http\ClientInterface $httpClient
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        Http\ClientInterface $httpClient,
        ScopeConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Performs obtaining of an OTP from the MBI service.
     *
     * Returns received OTP or FALSE in case of failure.
     *
     * @return string|false
     */
    public function call()
    {
        $otp = false;

        if ($this->analyticsToken->isTokenExist()) {
            try {
                $response = $this->httpClient->request(
                    ZendClient::POST,
                    $this->config->getValue($this->otpUrlConfigPath),
                    $this->getRequestJson(),
                    ['Content-Type: application/json']
                );

                if ($response) {
                    $otp = $this->extractOtp($response);

                    if (!$otp) {
                        $this->logger->warning(
                            sprintf(
                                'Obtaining of an OTP from the MBI service has been failed: %s',
                                !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                            )
                        );
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return $otp;
    }

    /**
     * Prepares request data in JSON format.
     *
     * @return string
     */
    private function getRequestJson()
    {
        return json_encode(
            [
                "access-token" => $this->analyticsToken->getToken(),
                "url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
            ]
        );
    }

    /**
     * Extracts an OTP from the response.
     *
     * Returns the OTP or FALSE if the OTP is not found.
     *
     * @param HttpResponse $response
     * @return string|false
     */
    private function extractOtp(HttpResponse $response)
    {
        $otp = false;

        if ($response->getStatus() === 201) {
            $body = json_decode($response->getBody(), 1);

            $otp = !empty($body['otp']) ? $body['otp'] : false;
        }

        return $otp;
    }
}
