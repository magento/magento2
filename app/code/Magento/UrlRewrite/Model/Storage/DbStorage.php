<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\UrlRewrite\Model\OptionProvider;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Url rewrites DB storage.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DbStorage extends AbstractStorage
{
    /**
     * DB Storage table name
     */
    public const TABLE_NAME = 'url_rewrite';

    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    public const ERROR_CODE_DUPLICATE_ENTRY = 1062;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param UrlRewriteFactory    $urlRewriteFactory
     * @param DataObjectHelper     $dataObjectHelper
     * @param ResourceConnection   $resource
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource,
        LoggerInterface $logger = null
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);

        parent::__construct($urlRewriteFactory, $dataObjectHelper);
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param  array $data
     * @return Select
     */
    protected function prepareSelect(array $data)
    {
        $select = $this->connection->select();
        $select->from($this->resource->getTableName(self::TABLE_NAME));

        foreach ($data as $column => $value) {
            $select->where($this->connection->quoteIdentifier($column) . ' IN (?)', $value);
        }

        return $select;
    }

    /**
     * @inheritdoc
     */
    protected function doFindAllByData(array $data)
    {
        return $this->connection->fetchAll($this->prepareSelect($data));
    }

    /**
     * @inheritdoc
     */
    protected function doFindOneByData(array $data)
    {
        if (array_key_exists(UrlRewrite::REQUEST_PATH, $data)
            && is_string($data[UrlRewrite::REQUEST_PATH])
        ) {
            $result = null;

            $requestPath = $data[UrlRewrite::REQUEST_PATH];
            $decodedRequestPath = urldecode($requestPath);
            $data[UrlRewrite::REQUEST_PATH] = array_unique(
                [
                rtrim($requestPath, '/'),
                rtrim($requestPath, '/') . '/',
                rtrim($decodedRequestPath, '/'),
                rtrim($decodedRequestPath, '/') . '/',
                ]
            );

            $resultsFromDb = $this->connection->fetchAll($this->prepareSelect($data));
            if ($resultsFromDb) {
                $urlRewrite = $this->extractMostRelevantUrlRewrite($requestPath, $resultsFromDb);
                $result = $this->prepareUrlRewrite($requestPath, $urlRewrite);
            }

            return $result;
        }

        return $this->connection->fetchRow($this->prepareSelect($data));
    }

    /**
     * Extract most relevant url rewrite from url rewrites list
     *
     * @param  string $requestPath
     * @param  array  $urlRewrites
     * @return array|null
     */
    private function extractMostRelevantUrlRewrite(string $requestPath, array $urlRewrites): ?array
    {
        $prioritizedUrlRewrites = [];
        foreach ($urlRewrites as $urlRewrite) {
            $urlRewriteRequestPath = $urlRewrite[UrlRewrite::REQUEST_PATH];
            $urlRewriteTargetPath = $urlRewrite[UrlRewrite::TARGET_PATH] ?? '';
            $trimmedUrlRewriteRequestPath = rtrim($urlRewriteRequestPath ?? '', '/');
            switch (true) {
                case $trimmedUrlRewriteRequestPath === rtrim($urlRewriteTargetPath, '/'):
                    $priority = 99;
                    break;
                case $urlRewriteRequestPath === $requestPath:
                    $priority = 1;
                    break;
                case $urlRewriteRequestPath === urldecode($requestPath):
                    $priority = 2;
                    break;
                case $trimmedUrlRewriteRequestPath === rtrim($requestPath, '/'):
                    $priority = 3;
                    break;
                case $trimmedUrlRewriteRequestPath === rtrim(urldecode($requestPath), '/'):
                    $priority = 4;
                    break;
                default:
                    $priority = 5;
                    break;
            }
            $prioritizedUrlRewrites[$priority] = $urlRewrite;
        }
        ksort($prioritizedUrlRewrites);

        return array_shift($prioritizedUrlRewrites);
    }

    /**
     * Prepare url rewrite
     *
     * If request path matches the DB value or it's redirect - we can return result from DB
     * Otherwise return 301 redirect to request path from DB results
     *
     * @param  string $requestPath
     * @param  array  $urlRewrite
     * @return array
     */
    private function prepareUrlRewrite(string $requestPath, array $urlRewrite): array
    {
        $redirectTypes = [OptionProvider::TEMPORARY, OptionProvider::PERMANENT];
        $canReturnResultFromDb = (
            in_array($urlRewrite[UrlRewrite::REQUEST_PATH], [$requestPath, urldecode($requestPath)], true)
            || in_array((int) $urlRewrite[UrlRewrite::REDIRECT_TYPE], $redirectTypes, true)
        );
        if (!$canReturnResultFromDb) {
            $urlRewrite = [
                UrlRewrite::ENTITY_TYPE => 'custom',
                UrlRewrite::ENTITY_ID => '0',
                UrlRewrite::REQUEST_PATH => $requestPath,
                UrlRewrite::TARGET_PATH => $urlRewrite[UrlRewrite::REQUEST_PATH],
                UrlRewrite::REDIRECT_TYPE => OptionProvider::PERMANENT,
                UrlRewrite::STORE_ID => $urlRewrite[UrlRewrite::STORE_ID],
                UrlRewrite::DESCRIPTION => null,
                UrlRewrite::IS_AUTOGENERATED => '0',
                UrlRewrite::METADATA => null,
            ];
        }

        return $urlRewrite;
    }

    /**
     * Delete old URLs from DB.
     *
     * @param  UrlRewrite[] $urls
     * @return void
     */
    private function deleteOldUrls(array $urls): void
    {
        $oldUrlsSelect = $this->connection->select();
        $oldUrlsSelect->from(
            $this->resource->getTableName(self::TABLE_NAME)
        );

        $uniqueEntities = $this->prepareUniqueEntities($urls);
        foreach ($uniqueEntities as $storeId => $entityTypes) {
            foreach ($entityTypes as $entityType => $entities) {
                $oldUrlsSelect->orWhere(
                    $this->connection->quoteIdentifier(
                        UrlRewrite::STORE_ID
                    ) . ' = ' . $this->connection->quote($storeId, 'INTEGER') .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_ID
                    ) . ' IN (' . $this->connection->quote($entities, 'INTEGER') . ')' .
                    ' AND ' . $this->connection->quoteIdentifier(
                        UrlRewrite::ENTITY_TYPE
                    ) . ' = ' . $this->connection->quote($entityType)
                );
            }
        }

        // prevent query locking in a case when nothing to delete
        $checkOldUrlsSelect = clone $oldUrlsSelect;
        $checkOldUrlsSelect->reset(Select::COLUMNS);
        $checkOldUrlsSelect->columns('count(*)');
        $hasOldUrls = (bool)$this->connection->fetchOne($checkOldUrlsSelect);

        if ($hasOldUrls) {
            $this->connection->query(
                $oldUrlsSelect->deleteFromSelect(
                    $this->resource->getTableName(self::TABLE_NAME)
                )
            );
        }
    }

    /**
     * Prepare array with unique entities
     *
     * @param  UrlRewrite[] $urls
     * @return array
     */
    private function prepareUniqueEntities(array $urls): array
    {
        $uniqueEntities = [];
        /** @var UrlRewrite $url */
        foreach ($urls as $url) {
            $entityIds = (!empty($uniqueEntities[$url->getStoreId()][$url->getEntityType()])) ?
                $uniqueEntities[$url->getStoreId()][$url->getEntityType()] : [];

            if (!\in_array($url->getEntityId(), $entityIds)) {
                $entityIds[] = $url->getEntityId();
            }
            $uniqueEntities[$url->getStoreId()][$url->getEntityType()] = $entityIds;
        }

        return $uniqueEntities;
    }

    /**
     * @inheritDoc
     */
    protected function doReplace(array $urls): array
    {
        $this->connection->beginTransaction();

        try {
            $this->deleteOldUrls($urls);

            $data = [];
            foreach ($urls as $url) {
                $data[] = $url->toArray();
            }

            $this->insertMultiple($data);

            $this->connection->commit();
            // @codingStandardsIgnoreStart
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            // @codingStandardsIgnoreEnd
            $this->connection->rollBack();

            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewrite::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewrite::STORE_ID => $url->getStoreId(),
                    ]
                );
                if (isset($urlFound[UrlRewrite::URL_REWRITE_ID])) {
                    $urlConflicted[$urlFound[UrlRewrite::URL_REWRITE_ID]] = $url->toArray();
                }
            }
            if ($urlConflicted) {
                throw new \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException(
                    __('URL key for specified store already exists.'),
                    $e,
                    $e->getCode(),
                    $urlConflicted
                );
            } else {
                throw $e->getPrevious() ?: $e;
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $urls;
    }

    /**
     * Insert multiple
     *
     * @param  array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException|\Exception
     * @throws \Exception
     */
    protected function insertMultiple($data): void
    {
        try {
            $this->connection->insertMultiple($this->resource->getTableName(self::TABLE_NAME), $data);
        } catch (\Exception $e) {
            if (($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY)
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.'),
                    $e
                );
            }
            throw $e;
        }
    }

    /**
     * Get filter for url rows deletion due to provided urls
     *
     * @param      UrlRewrite[] $urls
     * @return     array
     * @deprecated 101.0.3 Not used anymore.
     */
    protected function createFilterDataBasedOnUrls($urls): array
    {
        $data = [];
        foreach ($urls as $url) {
            $entityType = $url->getEntityType();
            foreach ([UrlRewrite::ENTITY_ID, UrlRewrite::STORE_ID] as $key) {
                $fieldValue = $url->getByKey($key);
                if (!isset($data[$entityType][$key]) || !in_array($fieldValue, $data[$entityType][$key])) {
                    $data[$entityType][$key][] = $fieldValue;
                }
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function deleteByData(array $data)
    {
        $this->connection->query(
            $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
        );
    }
}
