<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch;
use Psr\Log\LoggerInterface;

class ConnectionManager
{
    /**
     * @var Elasticsearch
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientFactoryInterface
     */
    protected $clientFactory;

    /**
     * @var ClientOptionsInterface
     */
    protected $clientConfig;

    /**
     * @param ClientFactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
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
     * @param array $options
     * @throws \RuntimeException
     * @return Elasticsearch
     */
    public function getConnection($options = [])
    {
        if (!$this->client) {
            $this->connect($options);
        }

        return $this->client;
    }

    /**
     * Connect to Elasticsearch client with default options
     *
     * @param array $options
     * @throws \RuntimeException
     * @return void
     */
    private function connect($options)
    {
        try {
            $this->client = $this->clientFactory->create($this->clientConfig->prepareClientOptions($options));
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \RuntimeException('Elasticsearch client is not set.');
        }
    }
}
