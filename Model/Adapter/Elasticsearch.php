<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\Model\ResourceModel\Index;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
use Magento\Framework\Exception\LocalizedException;
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
     * @var FieldMapper
     */
    protected $fieldMapper;

    /**
     * @var ClientOptionsInterface
     */
    protected $clientConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ConnectionManager $connectionManager
     * @param Index $resourceIndex
     * @param AttributeContainer $attributeContainer
     * @param DocumentDataMapper $documentDataMapper
     * @param FieldMapper $fieldMapper
     * @param ClientOptionsInterface $clientConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConnectionManager $connectionManager,
        Index $resourceIndex,
        AttributeContainer $attributeContainer,
        DocumentDataMapper $documentDataMapper,
        FieldMapper $fieldMapper,
        ClientOptionsInterface $clientConfig,
        LoggerInterface $logger
    ) {
        $this->connectionManager = $connectionManager;
        $this->resourceIndex = $resourceIndex;
        $this->attributeContainer = $attributeContainer;
        $this->documentDataMapper = $documentDataMapper;
        $this->fieldMapper = $fieldMapper;
        $this->clientConfig = $clientConfig;
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
                $this->connectionManager->getConnection()->bulkQuery($bulkIndexDocuments);
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
        $indexName = $this->clientConfig->getIndexName();
        $entityType = $this->clientConfig->getEntityType();
        $documentIds = $this->connectionManager->getConnection()->getAllIds($indexName, $entityType);
        $this->deleteDocs($documentIds);

        return $this;
    }

    /**
     * Delete documents from Elasticsearch index by Ids
     *
     * @param array $documentIds
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds)
    {
        try {
            $bulkDeleteDocuments = $this->getDocsArrayInBulkIndexFormat($documentIds, self::BULK_ACTION_DELETE);
            $this->connectionManager->getConnection()->bulkQuery($bulkDeleteDocuments);
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
     * @param string $action
     * @return array
     */
    protected function getDocsArrayInBulkIndexFormat($documents, $action = self::BULK_ACTION_INDEX)
    {
        $indexName = $this->clientConfig->getIndexName();
        $entityType = $this->clientConfig->getEntityType();
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
            } elseif ($action == self::BULK_ACTION_UPDATE) {
                $bulkArray['body'][] = ['doc' => $document];
            }
        }

        return $bulkArray;
    }

    /**
     * Checks whether Elasticsearch index exists. If not - creates one and put mapping.
     *
     * @return void
     */
    public function checkIndex()
    {
        $indexName = $this->clientConfig->getIndexName();
        $entityType = $this->clientConfig->getEntityType();
        if (!$this->connectionManager->getConnection()->indexExists($indexName)) {
            $this->connectionManager->getConnection()->createIndex($indexName);
            $this->connectionManager->getConnection()->addFieldsMapping(
                $this->fieldMapper->getAllAttributesTypes(),
                $indexName,
                $entityType
            );
        }
    }
}
