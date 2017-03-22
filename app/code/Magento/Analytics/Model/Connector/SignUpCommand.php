<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector;

use Magento\Analytics\Model\AnalyticsToken;
use Magento\Analytics\Model\IntegrationManager;
use Magento\Config\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Class SignUpCommand
 *
 * SignUp merchant for Free Tier project
 */
class SignUpCommand implements CommandInterface
{
    /**
     * @var string
     */
    private $signUpUrlPath = 'analytics/url/signup';

    /**
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * @var IntegrationManager
     */
    private $integrationManager;

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
     * SignUpCommand constructor.
     *
     * @param SignUpRequest $signUpRequest
     * @param AnalyticsToken $analyticsToken
     * @param IntegrationManager $integrationManager
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        IntegrationManager $integrationManager,
        Config $config,
        Http\ClientInterface $httpClient,
        LoggerInterface $logger
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->integrationManager = $integrationManager;
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    /**
     * Executes signUp command
     *
     * During this call Magento generates or retrieves access token for the integration user
     * In case successful generation Magento activates user and sends access token to MA
     * As the response, Magento receives a token to MA
     * Magento stores this token in System Configuration
     *
     * This method returns true in case of success
     *
     * @return bool
     */
    public function execute()
    {
        $result = false;
        $integrationToken = $this->integrationManager->generateToken();
        if ($integrationToken) {
            $this->integrationManager->activateIntegration();
            $response = $this->httpClient->request(
                ZendClient::POST,
                $this->config->getConfigDataValue($this->signUpUrlPath),
                [
                    "token" => $integrationToken->getToken(),
                    "url" => $this->config->getConfigDataValue(
                        Store::XML_PATH_SECURE_BASE_URL
                    )
                ]
            );

            $result = $this->parseResult($response);
            if ($result) {
                $this->analyticsToken->storeToken($result);
            }
        }
        return $result;
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
