<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Model\Apm;

use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Request;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\NewRelicReporting\Model\Config;
use Psr\Log\LoggerInterface;

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
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LaminasClientFactory $clientFactory
     */
    protected $clientFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param Config $config
     * @param LoggerInterface $logger
     * @param LaminasClientFactory $clientFactory
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        LaminasClientFactory $clientFactory,
        SerializerInterface $serializer
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->serializer = $serializer;
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

        /** @var LaminasClient $client */
        $client = $this->clientFactory->create();
        $client->setUri($apiUrl);
        $client->setMethod(Request::METHOD_POST);
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
        $client->setRawBody($this->serializer->serialize($params));

        try {
            $response = $client->send();
        } catch (RuntimeException $e) {
            $this->logger->critical($e);
            return false;
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 210) {
            $this->logger->warning('Deployment marker request did not send a 200 status code.');
            return false;
        }

        return $response->getBody();
    }
}
