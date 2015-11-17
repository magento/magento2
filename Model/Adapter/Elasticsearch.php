<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Framework\Exception\LocalizedException;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Config;
use Psr\Log\LoggerInterface;

/**
 * Elasticsearch adapter
 */
class Elasticsearch
{
    /**#@+
     * Text flags for Elasticsearch bulk actions
     */
    const BULK_ACTION_INDEX = 'index';
    const BULK_ACTION_CREATE = 'create';
    const BULK_ACTION_DELETE = 'delete';
    const BULK_ACTION_UPDATE = 'update';
    /**#@-*/

    /**
     * @var ConnectionManager
     */
    protected $connectionManager;

    /**
     * @var Index
     */
    protected $resourceIndex;

    /**
     * @var AttributeContainer
     */
    protected $attributeContainer;

    /**
     * @var DocumentDataMapper
     */
    protected $documentDataMapper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ConnectionManager $connectionManager
     * @param Index $resourceIndex
     * @param AttributeContainer $attributeContainer
     * @param DocumentDataMapper $documentDataMapper
     * @param Config $config
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Index $resourceIndex,
        AttributeContainer $attributeContainer,
        DocumentDataMapper $documentDataMapper,
        Config $config,
        LoggerInterface $logger
    ) {
        $this->connectionManager = $connectionManager;
        $this->resourceIndex = $resourceIndex;
        $this->attributeContainer = $attributeContainer;
        $this->documentDataMapper = $documentDataMapper;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Retrieve Elasticsearch server status
     *
     * @return bool
     * @throws LocalizedException
     */
    public function ping()
    {
        try {
            $response = $this->connectionManager->getConnection()->ping();
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Could not ping search engine: %1', $e->getMessage())
            );
        }
        return $response;
    }

    /**
     * Create Elasticsearch documents by specified data
     *
     * @param array $documentData
     * @param int $storeId
     * @return array
     */
    public function prepareDocsPerStore(array $documentData, $storeId)
    {
        if (0 === count($documentData)) {
            return [];
        }

        $documents = [];
        $productIds = array_keys($documentData);
        $priceIndexData = $this->attributeContainer->getAttribute('price')
            ? $this->resourceIndex->getPriceIndexData($productIds, $storeId)
            : [];
        $categoryIndexData = $this->resourceIndex->getCategoryProductIndexData($storeId, $productIds);
        $fullProductIndexData = $this->resourceIndex->getFullProductIndexData($productIds);

        foreach ($fullProductIndexData as $productId => $productIndexData) {
            $document = $this->documentDataMapper->map(
                $productIndexData,
                $productId,
                $storeId,
                $priceIndexData,
                $categoryIndexData
            );
            $documents[$productId] = $document;
        }

        return $documents;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     *
     * @param array $documents
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $documents)
    {
        if (count($documents)) {
            try {
                $bulkIndexDocuments = $this->getDocsArrayInBulkIndexFormat($documents);
                $this->connectionManager->getConnection()->addDocuments($bulkIndexDocuments);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Removes all documents from Elasticsearch index
     *
     * @return $this
     * @throws \Exception
     */
    public function cleanIndex()
    {
        try {
            $indexName = $this->config->getIndexName();
            $entityType = $this->config->getEntityType();
            $this->connectionManager->getConnection()->deleteDocumentsFromIndex($indexName, $entityType);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     *
     * @param array $documentIds
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds)
    {
        try {
            $indexName = $this->config->getIndexName();
            $entityType = $this->config->getEntityType();
            $this->connectionManager->getConnection()->deleteDocumentsByIds($documentIds, $indexName, $entityType);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Reformat documents array to bulk index format
     *
     * @param $documents
     * @param string $action
     * @return array
     */
    protected function &getDocsArrayInBulkIndexFormat(&$documents, $action = self::BULK_ACTION_INDEX)
    {
        $indexName = $this->config->getIndexName();
        $entityType = $this->config->getEntityType();
        $bulkArray = [
            'index' => $indexName,
            'type' => $entityType,
            'body' => [],
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_type' => $entityType,
                    '_index' => $indexName
                ]
            ];
            $bulkArray['body'][] = $document;
        }

        return $bulkArray;
    }
}
