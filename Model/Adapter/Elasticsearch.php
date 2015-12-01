<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\Model\Client\Elasticsearch as ElasticsearchClient;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Elasticsearch adapter
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var FieldMapper
     */
    protected $fieldMapper;

    /**
     * @var ClientOptionsInterface
     */
    protected $clientConfig;

    /**
     * @var ElasticsearchClient
     */
    protected $client;

    /**
     * @var BuilderInterface
     */
    protected $indexBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $preparedIndex = [];

    /**
     * @param ConnectionManager      $connectionManager
     * @param Index                  $resourceIndex
     * @param AttributeContainer     $attributeContainer
     * @param DocumentDataMapper     $documentDataMapper
     * @param FieldMapper            $fieldMapper
     * @param ClientOptionsInterface $clientConfig
     * @param BuilderInterface       $indexBuilder
     * @param LoggerInterface        $logger
     * @param array                  $options
     *
     * @throws LocalizedException
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Index $resourceIndex,
        AttributeContainer $attributeContainer,
        DocumentDataMapper $documentDataMapper,
        FieldMapper $fieldMapper,
        ClientOptionsInterface $clientConfig,
        BuilderInterface $indexBuilder,
        LoggerInterface $logger,
        $options = []
    ) {
        $this->connectionManager = $connectionManager;
        $this->resourceIndex = $resourceIndex;
        $this->attributeContainer = $attributeContainer;
        $this->documentDataMapper = $documentDataMapper;
        $this->fieldMapper = $fieldMapper;
        $this->clientConfig = $clientConfig;
        $this->indexBuilder = $indexBuilder;
        $this->logger = $logger;

        try {
            $this->connect($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('We were unable to perform the search because of a search engine misconfiguration.')
            );
        }
    }

    /**
     * Connect to Search Engine Client by specified options.
     *
     * @param array $options
     * @return ElasticsearchClient
     */
    protected function connect($options = [])
    {
        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \RuntimeException('Elasticsearch client is not set.');
        }

        return $this->client;
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
            $response = $this->client->ping();
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
        $categoryIndexData = $this->resourceIndex->getFullCategoryProductIndexData($storeId, $productIds);
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
     * @param int $storeId
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $documents, $storeId)
    {
        if (count($documents)) {
            try {
                $this->checkIndex($storeId, false);
                $indexName = $this->getIndexName($storeId);
                $entityType = $this->clientConfig->getEntityType();
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
     * @return $this
     * @throws \Exception
     */
    public function cleanIndex($storeId)
    {
        $this->checkIndex($storeId);
        $indexName = $this->getIndexName($storeId);
        if ($this->client->isEmptyIndex($indexName)) {
            // use existing index if empty
            return $this;
        }

        // prepare new index name and increase version
        $indexPattern = $this->getIndexPattern($storeId);
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
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds, $storeId)
    {
        try {
            $this->checkIndex($storeId, false);
            $indexName = $this->getIndexName($storeId);
            $entityType = $this->clientConfig->getEntityType();
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
     * @return $this
     */
    protected function checkIndex($storeId, $checkAlias = true)
    {
        // create new index for store
        $indexName = $this->getIndexName($storeId);
        if (!$this->client->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName);
        }

        // add index to alias
        if ($checkAlias) {
            $namespace = $this->getIndexNamespace();
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
     *
     * @return $this
     */
    public function updateAlias($storeId)
    {
        if (!isset($this->preparedIndex[$storeId])) {
            return $this;
        }

        $oldIndex = $this->getIndexFromAlias($storeId);
        if ($oldIndex == $this->preparedIndex[$storeId]) {
            $oldIndex = '';
        }
        $this->client->updateAlias(
            $this->getIndexNamespace(),
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
     *
     * @return $this
     */
    protected function prepareIndex($storeId, $indexName)
    {
        $this->indexBuilder->setStoreId($storeId);
        $this->client->createIndex(
            $indexName,
            ['settings' => $this->indexBuilder->build()]
        );
        $this->client->addFieldsMapping(
            $this->fieldMapper->getAllAttributesTypes(),
            $indexName,
            $this->clientConfig->getEntityType()
        );
        $this->preparedIndex[$storeId] = $indexName;
        return $this;
    }

    /**
     * Get index namespace from config.
     *
     * @return string
     */
    protected function getIndexNamespace()
    {
        return $this->clientConfig->getIndexName();
    }

    /**
     * Returns the index name.
     *
     * @param int $storeId
     * @return string
     */
    protected function getIndexName($storeId)
    {
        if (isset($this->preparedIndex[$storeId])) {
            return $this->preparedIndex[$storeId];
        } else {
            $indexName = $this->getIndexFromAlias($storeId);
            if (empty($indexName)) {
                $indexName = $this->getIndexPattern($storeId) . 1;
            }
        }
        return $indexName;
    }

    /**
     * Returns index pattern.
     *
     * @param int $storeId
     *
     * @return string
     */
    protected function getIndexPattern($storeId)
    {
        return $this->getIndexNamespace() . '_' . $storeId . '_v';
    }

    /**
     * Returns index for store in alias definition.
     *
     * @param int $storeId
     *
     * @return string
     */
    protected function getIndexFromAlias($storeId)
    {
        $storeIndex = '';
        $indexPattern = $this->getIndexPattern($storeId);
        $namespace = $this->getIndexNamespace();
        if ($this->client->existsAlias($namespace)) {
            $alias = $this->client->getAlias($namespace);
            $indices = array_keys($alias);
            foreach ($indices as $index) {
                if (strpos($index, $indexPattern) === 0) {
                    $storeIndex = $index;
                    break;
                }
            }
        }
        return $storeIndex;
    }
}
