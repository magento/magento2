<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;

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
     * @var DocumentDataMapper
     */
    protected $documentDataMapper;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver
     */
    protected $indexNameResolver;

    /**
     * @var FieldMapper
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Elasticsearch\Model\Config
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface
     */
    protected $indexBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $preparedIndex = [];

    /**
     * Constructor for Elasticsearch adapter.
     *
     * @param ConnectionManager $connectionManager
     * @param DocumentDataMapper $documentDataMapper
     * @param FieldMapper $fieldMapper
     * @param \Magento\Elasticsearch\Model\Config $clientConfig
     * @param \Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface $indexBuilder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver
     * @param array $options
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ConnectionManager $connectionManager,
        DocumentDataMapper $documentDataMapper,
        FieldMapper $fieldMapper,
        \Magento\Elasticsearch\Model\Config $clientConfig,
        \Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface $indexBuilder,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver $indexNameResolver,
        $options = []
    ) {
        $this->connectionManager = $connectionManager;
        $this->documentDataMapper = $documentDataMapper;
        $this->fieldMapper = $fieldMapper;
        $this->clientConfig = $clientConfig;
        $this->indexBuilder = $indexBuilder;
        $this->logger = $logger;
        $this->indexNameResolve = $indexNameResolver;

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Retrieve Elasticsearch server status
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function ping()
    {
        try {
            $response = $this->client->ping();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
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
        $documents = [];
        if (count($documentData)) {
            foreach ($documentData as $documentId => $data) {
                $document = $this->documentDataMapper->map(
                    $documentId,
                    $data,
                    $storeId
                );
                $documents[$documentId] = $document;
            }
        }
        return $documents;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     *
     * @param array $documents
     * @param int $storeId
     * @param string $entityType
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $documents, $storeId, $entityType)
    {
        if (count($documents)) {
            try {
                $this->checkIndex($storeId, false, $entityType);
                $indexName = $this->indexNameResolve->getIndexName($storeId, $entityType, $this->preparedIndex);
                $bulkIndexDocuments = $this->getDocsArrayInBulkIndexFormat($documents, $indexName, $entityType);
                $this->client->bulkQuery($bulkIndexDocuments);
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
     * @param int $storeId
     * @param string $entityType
     * @return $this
     */
    public function cleanIndex($storeId, $entityType)
    {
        $this->checkIndex($storeId, true, $entityType);
        $indexName = $this->indexNameResolve->getIndexName($storeId, $entityType, $this->preparedIndex);
        if ($this->client->isEmptyIndex($indexName)) {
            // use existing index if empty
            return $this;
        }

        // prepare new index name and increase version
        $indexPattern = $this->indexNameResolve->getIndexPattern($storeId, $entityType);
        $version = intval(str_replace($indexPattern, '', $indexName));
        $newIndexName = $indexPattern . ++$version;

        // remove index if already exists
        if ($this->client->indexExists($newIndexName)) {
            $this->client->deleteIndex($newIndexName);
        }

        // prepare new index
        $this->prepareIndex($storeId, $newIndexName);

        return $this;
    }

    /**
     * Delete documents from Elasticsearch index by Ids
     *
     * @param array $documentIds
     * @param int $storeId
     * @param string $entityType
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds, $storeId, $entityType)
    {
        try {
            $this->checkIndex($storeId, false, $entityType);
            $indexName = $this->indexNameResolve->getIndexName($storeId, $entityType, $this->preparedIndex);
            $bulkDeleteDocuments = $this->getDocsArrayInBulkIndexFormat(
                $documentIds,
                $indexName,
                $entityType,
                self::BULK_ACTION_DELETE
            );
            $this->client->bulkQuery($bulkDeleteDocuments);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }

        return $this;
    }

    /**
     * Reformat documents array to bulk format
     *
     * @param array $documents
     * @param string $indexName
     * @param string $entityType
     * @param string $action
     * @return array
     */
    protected function getDocsArrayInBulkIndexFormat(
        $documents,
        $indexName,
        $entityType,
        $action = self::BULK_ACTION_INDEX
    ) {
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
            if ($action == self::BULK_ACTION_INDEX) {
                $bulkArray['body'][] = $document;
            }
        }

        return $bulkArray;
    }

    /**
     * Checks whether Elasticsearch index and alias exists.
     *
     * @param int $storeId
     * @param bool $checkAlias
     * @param string $entityType
     * @return $this
     */
    protected function checkIndex($storeId, $checkAlias = true, $entityType)
    {
        // create new index for store
        $indexName = $this->indexNameResolve->getIndexName($storeId, $entityType, $this->preparedIndex);
        if (!$this->client->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName);
        }

        // add index to alias
        if ($checkAlias) {
            $namespace = $this->indexNameResolve->getIndexNameForAlias($storeId, $entityType);
            if (!$this->client->existsAlias($namespace, $indexName)) {
                $this->client->updateAlias($namespace, $indexName);
            }
        }
        return $this;
    }

    /**
     * Update Elasticsearch alias for new index.
     *
     * @param int $storeId
     * @param string $entityType
     * @return $this
     */
    public function updateAlias($storeId, $entityType)
    {
        if (!isset($this->preparedIndex[$storeId])) {
            return $this;
        }

        $oldIndex = $this->indexNameResolve->getIndexFromAlias($storeId, $entityType);
        if ($oldIndex == $this->preparedIndex[$storeId]) {
            $oldIndex = '';
        }

        $this->client->updateAlias(
            $this->indexNameResolve->getIndexNameForAlias($storeId, $entityType),
            $this->preparedIndex[$storeId],
            $oldIndex
        );

        // remove obsolete index
        if ($oldIndex) {
            $this->client->deleteIndex($oldIndex);
        }

        return $this;
    }

    /**
     * Create new index with mapping.
     *
     * @param int $storeId
     * @param string $indexName
     * @return $this
     */
    protected function prepareIndex($storeId, $indexName)
    {
        $this->indexBuilder->setStoreId($storeId);
        $this->client->createIndex($indexName, ['settings' => $this->indexBuilder->build()]);
        $this->client->addFieldsMapping(
            $this->fieldMapper->getAllAttributesTypes(),
            $indexName,
            $this->clientConfig->getEntityType()
        );
        $this->preparedIndex[$storeId] = $indexName;
        return $this;
    }
}
