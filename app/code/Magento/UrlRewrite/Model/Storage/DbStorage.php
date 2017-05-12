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
use Magento\Framework\UrlInterface;

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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory $urlRewriteFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param \Magento\Framework\UrlInterface|null $urlBuilder
     */
    public function __construct(
        UrlRewriteFactory $urlRewriteFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceConnection $resource,
        LoggerInterface $logger = null,
        UrlInterface $urlBuilder = null
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Psr\Log\LoggerInterface::class);
        $this->urlBuilder = $urlBuilder ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\UrlInterface::class);

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
        /** @var \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $urlConflicted */
        $urlConflicted = [];
        foreach ($urls as $url) {
            if (!$this->insertUrl($url->toArray())) {
                $urlConflicted[] = $url;
            }
        }
        if (!empty($urlConflicted)) {
            $urlsWithLinks = '<br />';
            foreach ($urlConflicted as $url) {
                $urlFound = $this->doFindOneByData(
                    [
                        UrlRewriteData::REQUEST_PATH => $url->getRequestPath(),
                        UrlRewriteData::STORE_ID => $url->getStoreId()
                    ]
                );
                $adminEditUrl = $this->urlBuilder->getUrl(
                    'adminhtml/url_rewrite/edit',
                    ['id' => $urlFound[UrlRewriteData::URL_REWRITE_ID]]
                );
                $urlsWithLinks .='- <a href="' . $adminEditUrl .'">'
                    . $url->getRequestPath() . '</a><br />';
            }

            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __(
                    '<h4>The value specified in the URL Key field would generate a URL that already exists.</h4>'
                    .'To resolve this conflict, you can either change the value of the URL Key field '
                    .' (located in the Search Engine Optimization section) to a unique value, '
                    . 'or change the URL Key fields in all locations listed below:%1',
                    $urlsWithLinks
                )
            );
        }

        return $urls;
    }

    /**
     * Insert multiple
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     * @deprecated
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
                    __('A conflict has occurred between the entity\'s URL(s) and other URL(s).')
                );
            }
            throw $e;
        }
    }

    /**
     * Inserts a url as array to database and returns conflict status
     *
     * @param array $data
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function insertUrl(array $data)
    {
        try {
            return $this->connection->insert($this->resource->getTableName(self::TABLE_NAME), $data) > 0;
        } catch (\Exception $e) {
            if (isset($data['request_path'])
                && isset($data['entity_type'])
                && isset($data['store_id'])
                && isset($data['entity_id'])
                && $e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
            ) {
                $this->logger->warning(
                    __(
                        'Could not insert a conflicting URL when trying to insert \'%1\' for %2 '
                        .'with entity id %3 on store %4',
                        $data['request_path'],
                        $data['entity_type'],
                        $data['entity_id'],
                        $data['store_id']
                    )
                );
                return false;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while inserting a url key.')
                );
            }
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
