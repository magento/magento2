<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model\Storage;

use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Framework\Api\DataObjectHelper;
use Psr\Log\LoggerInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewriteData;

class DbStorage extends AbstractStorage
{
    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'url_rewrite';

    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 1062;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource,
        LoggerInterface $logger = null
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);

        parent::__construct($urlRewriteFactory, $dataObjectHelper);
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param array $data
     * @return \Magento\Framework\DB\Select
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
     * {@inheritdoc}
     */
    protected function doFindAllByData(array $data)
    {
        return $this->connection->fetchAll($this->prepareSelect($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function doFindOneByData(array $data)
    {
        return $this->connection->fetchRow($this->prepareSelect($data));
    }

    /**
     * {@inheritdoc}
     */
    protected function doReplace(array $urls)
    {
        foreach ($this->createFilterDataBasedOnUrls($urls) as $type => $urlData) {
            $urlData[UrlRewrite::ENTITY_TYPE] = $type;
            $this->deleteByData($urlData);
        }
        $data = [];
        foreach ($urls as $url) {
            $data[] = $url->toArray();
        }
        try {
            $this->insertMultiple($data);
        } catch (\Exception $e) {
            /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
            $urlConflicted = [];
            foreach ($urls as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewriteData::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewriteData::STORE_ID => $url->getStoreId()
                    ]
                );
                $urlConflicted[$urlFound[UrlRewriteData::URL_REWRITE_ID]] = $url;
            }
            if (!empty($urlConflicted)) {
                if ($e instanceof \Magento\Framework\Exception\AlreadyExistsException) {
                    throw new \Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException(
                        __('URL key for specified store already exists.'),
                        null,
                        $urlConflicted
                    );
                } else {
                    throw $e;
                }
            }
        }

        return $urls;
    }

    /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException|\Exception
     * @throws \Exception
     */
    protected function insertMultiple($data)
    {
        try {
            $this->connection->insertMultiple($this->resource->getTableName(self::TABLE_NAME), $data);
        } catch (\Exception $e) {
            if (($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY)
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                throw new \Magento\Framework\Exception\AlreadyExistsException(
                    __('URL key for specified store already exists.')
                );
            }
            throw $e;
        }
    }

    /**
     * Get filter for url rows deletion due to provided urls
     *
     * @param UrlRewrite[] $urls
     * @return array
     */
    protected function createFilterDataBasedOnUrls($urls)
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
     * {@inheritdoc}
     */
    public function deleteByData(array $data)
    {
        $this->connection->query(
            $this->prepareSelect($data)->deleteFromSelect($this->resource->getTableName(self::TABLE_NAME))
        );
    }
}
