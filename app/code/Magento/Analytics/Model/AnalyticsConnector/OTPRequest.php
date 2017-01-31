<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\AnalyticsConnector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\ZendClient as HttpClient;
use Magento\Framework\HTTP\ZendClientFactory as HttpClientFactory;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Zend_Http_Response as HttpResponse;

/**
 * Perform direct call to MBI services for getting OTP.
 *
 * OTP (One-Time Password) is a password that is valid for only one login session
 * and has limited time when it is valid.
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
     * @var HttpClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Path to config value with URL which provide OTP for MBI.
     *
     * @var string
     */
    private $otpUrlConfigPath = 'analytics/url/otp';

    /**
     * @param AnalyticsToken $analyticsToken
     * @param HttpClientFactory $clientFactory
     * @param ScopeConfigInterface $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        HttpClientFactory $clientFactory,
        ScopeConfigInterface $config,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->clientFactory = $clientFactory;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Performs call to MBI services for getting OTP.
     *
     * @return string|false OTP or false if request was unsuccessful.
     */
    public function call()
    {
        $otp = false;
        try {
            if ($this->analyticsToken->isTokenExist()) {
                /** @var HttpClient $client */
                $client = $this->clientFactory->create();
                $client->setUri($this->config->getValue($this->otpUrlConfigPath));
                $client->setRawData($this->getRequestJson());
                $client->setMethod(HttpClient::POST);
                $otp = $this->extractOtp($client->request());
                if (!$otp) {
                    $this->logger->critical('The request for a OTP is unsuccessful.');
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $otp;
    }

    /**
     * Prepares json string with data for request.
     *
     * @return string
     */
    private function getRequestJson()
    {
        return json_encode(
            [
                "token" => $this->analyticsToken->getToken(),
                "url" => $this->config->getValue(Store::XML_PATH_SECURE_BASE_URL),
            ]
        );
    }

    /**
     * Extracts OTP from the response.
     *
     * @param HttpResponse $response
     * @return string|false False if response doesn't contain required data.
     */
    private function extractOtp(HttpResponse $response)
    {
        $otp = false;
        if ($response->getStatus() === 200) {
            $body = json_decode($response->getBody(), 1);
            $otp = !empty($body['otp']) ? $body['otp'] : false;
        }

        return $otp;
    }
}
