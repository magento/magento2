<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\SearchAdapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Client\ClientFactoryInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch;
use Magento\Elasticsearch\Model\Adapter\FieldMapper;
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
     * @var FieldMapper
     */
    protected $fieldMapper;

    /**
     * @param ClientFactoryInterface $clientFactory
     * @param ClientOptionsInterface $clientConfig
     * @param FieldMapper $fieldMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientFactoryInterface $clientFactory,
        ClientOptionsInterface $clientConfig,
        FieldMapper $fieldMapper,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->clientFactory = $clientFactory;
        $this->clientConfig = $clientConfig;
        $this->fieldMapper = $fieldMapper;
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
            $this->checkIndex();
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

    /**
     * Checks whether Elasticsearch index exists. If not - creates one and put mapping.
     *
     * @return void
     */
    private function checkIndex()
    {
        $indexName = $this->clientConfig->getIndexName();
        $entityType = $this->clientConfig->getEntityType();
        if ($this->client->createIndexIfNotExists($indexName)) {
            $this->client->addFieldsMapping(
                $this->fieldMapper->getAllAttributesTypes(),
                $indexName,
                $entityType
            );
        }
    }
}
