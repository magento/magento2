<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClient;
use Zend_Http_Response as HttpResponse;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Representation of a 'SignUp' request.
 *
 * Prepares and sends the request to the MBI service, processes response.
 */
class SignUpRequest
{
    /**
     * @var string
     */
    private $signUpUrlPath = 'analytics/url/signup';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Http\ClientInterface
     */
    private $httpClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param Http\ClientInterface $httpClient
     * @param LoggerInterface $logger
     */
    public function __construct(
        Config $config,
        Http\ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Performs a 'signUp' call to MBI service.
     *
     * Returns MBI access token or FALSE in case of failure.
     *
     * @param string $integrationToken
     * @return string|false
     */
    public function call($integrationToken)
    {
        $response = $this->httpClient->request(
            ZendClient::POST,
            $this->config->getConfigDataValue($this->signUpUrlPath),
            [
                "token" => $integrationToken,
                "url" => $this->config->getConfigDataValue(
                    Store::XML_PATH_SECURE_BASE_URL
                )
            ]
        );

        return $this->parseResult($response);
    }

    /**
     * @param \Zend_Http_Response $response
     *
     * @return false|string
     */
    private function parseResult($response)
    {
        $result = false;
        if ($response) {
            if ($response->getStatus() === 201) {
                $body = json_decode($response->getBody(), 1);

                if (isset($body['access-token']) && !empty($body['access-token'])) {
                    $result = $body['access-token'];
                }
            }

            if (!$result) {
                $this->logger->warning(
                    sprintf(
                        'Subscription for MBI service has been failed. An error occurred during token exchange: %s',
                        !empty($response->getBody()) ? $response->getBody() : 'Response body is empty.'
                    )
                );
            }
        }

        return $result;
    }
}
