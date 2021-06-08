<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter;

use Elasticsearch\Common\Exceptions\Missing404Exception;
use Magento\AdvancedSearch\Model\Client\ClientInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\StaticField;
use Magento\Elasticsearch\Model\Adapter\Index\BuilderInterface;
use Magento\Elasticsearch\Model\Adapter\Index\IndexNameResolver;
use Magento\Elasticsearch\Model\Config;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\ArrayManager;
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
     * Buffer for total fields limit in mapping.
     */
    private const MAPPING_TOTAL_FIELDS_BUFFER_LIMIT = 1000;

    /**#@-*/
    protected $connectionManager;

    /**
     * @var IndexNameResolver
     */
    protected $indexNameResolver;

    /**
     * @var FieldMapperInterface
     */
    protected $fieldMapper;

    /**
     * @var Config
     */
    protected $clientConfig;

    /**
     * @var ClientInterface
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
     * @var BatchDataMapperInterface
     */
    private $batchDocumentDataMapper;

    /**
     * @var array
     */
    private $mappedAttributes = [];

    /**
     * @var string[]
     */
    private $indexByCode = [];

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $productAttributeRepository;

    /**
     * @var StaticField
     */
    private $staticFieldProvider;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ConnectionManager $connectionManager
     * @param FieldMapperInterface $fieldMapper
     * @param Config $clientConfig
     * @param Index\BuilderInterface $indexBuilder
     * @param LoggerInterface $logger
     * @param Index\IndexNameResolver $indexNameResolver
     * @param BatchDataMapperInterface $batchDocumentDataMapper
     * @param array $options
     * @param ProductAttributeRepositoryInterface|null $productAttributeRepository
     * @param StaticField|null $staticFieldProvider
     * @param ArrayManager|null $arrayManager
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ConnectionManager $connectionManager,
        FieldMapperInterface $fieldMapper,
        Config $clientConfig,
        BuilderInterface $indexBuilder,
        LoggerInterface $logger,
        IndexNameResolver $indexNameResolver,
        BatchDataMapperInterface $batchDocumentDataMapper,
        $options = [],
        ProductAttributeRepositoryInterface $productAttributeRepository = null,
        StaticField $staticFieldProvider = null,
        ArrayManager $arrayManager = null
    ) {
        $this->connectionManager = $connectionManager;
        $this->fieldMapper = $fieldMapper;
        $this->clientConfig = $clientConfig;
        $this->indexBuilder = $indexBuilder;
        $this->logger = $logger;
        $this->indexNameResolver = $indexNameResolver;
        $this->batchDocumentDataMapper = $batchDocumentDataMapper;
        $this->productAttributeRepository = $productAttributeRepository ?:
            ObjectManager::getInstance()->get(ProductAttributeRepositoryInterface::class);
        $this->staticFieldProvider = $staticFieldProvider ?:
            ObjectManager::getInstance()->get(StaticField::class);
        $this->arrayManager = $arrayManager ?:
            ObjectManager::getInstance()->get(ArrayManager::class);

        try {
            $this->client = $this->connectionManager->getConnection($options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new LocalizedException(
                __('The search failed because of a search engine misconfiguration.')
            );
        }
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
        $documents = [];
        if (count($documentData)) {
            $documents = $this->batchDocumentDataMapper->map(
                $documentData,
                $storeId
            );
        }
        return $documents;
    }

    /**
     * Add prepared Elasticsearch documents to Elasticsearch index
     *
     * @param array $documents
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws \Exception
     */
    public function addDocs(array $documents, $storeId, $mappedIndexerId)
    {
        if (count($documents)) {
            try {
                $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
                $bulkIndexDocuments = $this->getDocsArrayInBulkIndexFormat($documents, $indexName);
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
     * @param string $mappedIndexerId
     * @return $this
     */
    public function cleanIndex($storeId, $mappedIndexerId)
    {
        // needed to fix bug with double indices in alias because of second reindex in same process
        unset($this->preparedIndex[$storeId]);

        $this->checkIndex($storeId, $mappedIndexerId, true);
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);

        // prepare new index name and increase version
        $indexPattern = $this->indexNameResolver->getIndexPattern($storeId, $mappedIndexerId);
        $version = (int)(str_replace($indexPattern, '', $indexName));

        // compatibility with snapshotting collision
        $deleteQueue = [];
        do {
            $newIndexName = $indexPattern . (++$version);
            if ($this->client->indexExists($newIndexName)) {
                $deleteQueue[]= $newIndexName;
                $indexExists = true;
            } else {
                $indexExists = false;
            }
        } while ($indexExists);

        foreach ($deleteQueue as $indexToDelete) {
            // remove index if already exists, wildcard deletion may cause collisions
            try {
                $this->client->deleteIndex($indexToDelete);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        // prepare new index
        $this->prepareIndex($storeId, $newIndexName, $mappedIndexerId);

        return $this;
    }

    /**
     * Delete documents from Elasticsearch index by Ids
     *
     * @param array $documentIds
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return $this
     * @throws \Exception
     */
    public function deleteDocs(array $documentIds, $storeId, $mappedIndexerId)
    {
        try {
            $this->checkIndex($storeId, $mappedIndexerId, false);
            $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
            $bulkDeleteDocuments = $this->getDocsArrayInBulkIndexFormat(
                $documentIds,
                $indexName,
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
     * @param string $action
     * @return array
     */
    protected function getDocsArrayInBulkIndexFormat(
        $documents,
        $indexName,
        $action = self::BULK_ACTION_INDEX
    ) {
        $bulkArray = [
            'index' => $indexName,
            'type' => $this->clientConfig->getEntityType(),
            'body' => [],
            'refresh' => true,
        ];

        foreach ($documents as $id => $document) {
            $bulkArray['body'][] = [
                $action => [
                    '_id' => $id,
                    '_type' => $this->clientConfig->getEntityType(),
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
     * @param string $mappedIndexerId
     * @param bool $checkAlias
     *
     * @return $this
     */
    public function checkIndex(
        $storeId,
        $mappedIndexerId,
        $checkAlias = true
    ) {
        // create new index for store
        $indexName = $this->indexNameResolver->getIndexName($storeId, $mappedIndexerId, $this->preparedIndex);
        if (!$this->client->indexExists($indexName)) {
            $this->prepareIndex($storeId, $indexName, $mappedIndexerId);
        }

        // add index to alias
        if ($checkAlias) {
            $namespace = $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId);
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
     * @param string $mappedIndexerId
     * @return $this
     */
    public function updateAlias($storeId, $mappedIndexerId)
    {
        if (!isset($this->preparedIndex[$storeId])) {
            return $this;
        }

        $oldIndex = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        if ($oldIndex == $this->preparedIndex[$storeId]) {
            $oldIndex = '';
        }

        $this->client->updateAlias(
            $this->indexNameResolver->getIndexNameForAlias($storeId, $mappedIndexerId),
            $this->preparedIndex[$storeId],
            $oldIndex
        );

        // remove obsolete index
        if ($oldIndex) {
            try {
                $this->client->deleteIndex($oldIndex);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
            unset($this->indexByCode[$mappedIndexerId . '_' . $storeId]);
        }

        return $this;
    }

    /**
     * Update Elasticsearch mapping for index.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @param string $attributeCode
     * @return $this
     */
    public function updateIndexMapping(int $storeId, string $mappedIndexerId, string $attributeCode): self
    {
        $indexName = $this->getIndexFromAlias($storeId, $mappedIndexerId);
        if (empty($indexName)) {
            return $this;
        }

        try {
            $this->updateMapping($attributeCode, $indexName);
        } catch (Missing404Exception $e) {
            unset($this->indexByCode[$mappedIndexerId . '_' . $storeId]);
            $indexName = $this->getIndexFromAlias($storeId, $mappedIndexerId);
            $this->updateMapping($attributeCode, $indexName);
        }

        return $this;
    }

    /**
     * Retrieve index definition from class.
     *
     * @param int $storeId
     * @param string $mappedIndexerId
     * @return string
     */
    private function getIndexFromAlias(int $storeId, string $mappedIndexerId): string
    {
        $indexCode = $mappedIndexerId . '_' . $storeId;
        if (!isset($this->indexByCode[$indexCode])) {
            $this->indexByCode[$indexCode] = $this->indexNameResolver->getIndexFromAlias($storeId, $mappedIndexerId);
        }

        return $this->indexByCode[$indexCode];
    }

    /**
     * Retrieve mapped attributes from class.
     *
     * @param string $indexName
     * @return array
     */
    private function getMappedAttributes(string $indexName): array
    {
        if (empty($this->mappedAttributes[$indexName])) {
            $mappedAttributes = $this->client->getMapping(['index' => $indexName]);
            $pathField = $this->arrayManager->findPath('properties', $mappedAttributes);
            $this->mappedAttributes[$indexName] = $this->arrayManager->get($pathField, $mappedAttributes, []);
        }

        return $this->mappedAttributes[$indexName];
    }

    /**
     * Set mapped attributes to class.
     *
     * @param string $indexName
     * @param array $mappedAttributes
     * @return $this
     */
    private function setMappedAttributes(string $indexName, array $mappedAttributes): self
    {
        foreach ($mappedAttributes as $attributeCode => $attributeParams) {
            $this->mappedAttributes[$indexName][$attributeCode] = $attributeParams;
        }

        return $this;
    }

    /**
     * Create new index with mapping.
     *
     * @param int $storeId
     * @param string $indexName
     * @param string $mappedIndexerId
     * @return $this
     */
    protected function prepareIndex($storeId, $indexName, $mappedIndexerId)
    {
        $this->indexBuilder->setStoreId($storeId);
        $settings = $this->indexBuilder->build();
        $allAttributeTypes = $this->fieldMapper->getAllAttributesTypes(
            [
                'entityType' => $mappedIndexerId,
                // Use store id instead of website id from context for save existing fields mapping.
                // In future websiteId will be eliminated due to index stored per store
                'websiteId' => $storeId
            ]
        );
        $settings['index']['mapping']['total_fields']['limit'] = $this->getMappingTotalFieldsLimit($allAttributeTypes);
        $this->client->createIndex($indexName, ['settings' => $settings]);
        $this->client->addFieldsMapping(
            $allAttributeTypes,
            $indexName,
            $this->clientConfig->getEntityType()
        );
        $this->preparedIndex[$storeId] = $indexName;
        return $this;
    }

    /**
     * Get total fields limit for mapping.
     *
     * @param array $allAttributeTypes
     * @return int
     */
    private function getMappingTotalFieldsLimit(array $allAttributeTypes): int
    {
        $count = count($allAttributeTypes);
        foreach ($allAttributeTypes as $attributeType) {
            if (isset($attributeType['fields'])) {
                $count += count($attributeType['fields']);
            }
        }
        return $count + self::MAPPING_TOTAL_FIELDS_BUFFER_LIMIT;
    }

    /**
     * Perform index mapping update
     *
     * @param string $attributeCode
     * @param string $indexName
     * @return void
     */
    private function updateMapping(string $attributeCode, string $indexName): void
    {
        $attribute = $this->productAttributeRepository->get($attributeCode);
        $newAttributeMapping = $this->staticFieldProvider->getField($attribute);
        $mappedAttributes = $this->getMappedAttributes($indexName);
        $attrToUpdate = array_diff_key($newAttributeMapping, $mappedAttributes);
        if (!empty($attrToUpdate)) {
            $settings['index']['mapping']['total_fields']['limit'] = $this
                ->getMappingTotalFieldsLimit(array_merge($mappedAttributes, $attrToUpdate));
            $this->client->putIndexSettings($indexName, ['settings' => $settings]);

            $this->client->addFieldsMapping(
                $attrToUpdate,
                $indexName,
                $this->clientConfig->getEntityType()
            );
            $this->setMappedAttributes($indexName, $attrToUpdate);
        }
    }
}
