<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\AdvancedSearch\Model\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\FactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch;

class ConnectionManager
{
    /**
     * @var Elasticsearch
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FactoryInterface
     */
    private $clientFactory;

    /**
     * @var ClientOptionsInterface
     */
    private $clientConfig;

    /**
     * @param FactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        FactoryInterface $clientFactory,
        ClientOptionsInterface $clientConfig,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->clientConfig = $clientConfig;
    }

    /**
     * Get shared connection
     *
     * @throws \RuntimeException
     * @return Elasticsearch
     */
    public function getConnection()
    {
        if (!$this->client) {
            $this->connect();
        }

        return $this->client;
    }

    /**
     * Connect to Elasticsearch client with default options
     *
     * @throws \RuntimeException
     * @return void
     */
    private function connect()
    {
        try {
            $this->client = $this->clientFactory->create($this->clientConfig->prepareClientOptions());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \RuntimeException('Elasticsearch client is not set.');
        }
    }
}
