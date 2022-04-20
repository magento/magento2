<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Apm;

use \Magento\Framework\HTTP\ZendClient;

/**
 * Performs the request to make the deployment
 */
class Deployments
{
    /**
     * API URL for New Relic deployments
     */
    private const API_URL = 'https://api.newrelic.com/v2/applications/%s/deployments.json';

    /**
     * @var \Magento\NewRelicReporting\Model\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * Constructor
     *
     * @param \Magento\NewRelicReporting\Model\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\HTTP\ZendClientFactory $clientFactory
     */
    public function __construct(
        \Magento\NewRelicReporting\Model\Config $config,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\ZendClientFactory $clientFactory
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
    }

    /**
     * Performs the request to make the deployment
     *
     * @param string $description
     * @param bool $change
     * @param bool $user
     * @param ?string $revision
     *
     * @return bool|string
     */
    public function setDeployment($description, $change = false, $user = false, $revision = null)
    {
        $apiUrl = $this->config->getNewRelicApiUrl();
        if (empty($apiUrl)) {
            $this->logger->notice('New Relic API URL is blank, using fallback URL');
            $apiUrl = self::API_URL;
        }

        $apiUrl = sprintf($apiUrl, $this->config->getNewRelicAppId());

        /** @var \Magento\Framework\HTTP\ZendClient $client */
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setMethod(ZendClient::POST);

        $client->setHeaders(
            [
                'Api-Key' => $this->config->getNewRelicApiKey(),
                'Content-Type' => 'application/json'
            ]
        );

        if (!$revision) {
            $revision = hash('sha256', time());
        }

        $params = [
            'deployment' => [
                'description' => $description,
                'changelog' => $change,
                'user' => $user,
                'revision' => $revision
            ]
        ];

        $client->setParameterPost($params);

        try {
            $response = $client->request();
        } catch (\Zend_Http_Client_Exception $e) {
            $this->logger->critical($e);
            return false;
        }

        if ($response->getStatus() < 200 || $response->getStatus() > 210) {
            $this->logger->warning('Deployment marker request did not send a 200 status code.');
            return false;
        }

        return $response->getBody();
    }
}
