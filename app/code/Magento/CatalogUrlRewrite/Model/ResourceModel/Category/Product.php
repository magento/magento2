<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\UrlRewrite\Model\Storage\DbStorage;

/**
 * Class \Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product
 *
 * @since 2.0.0
 */
class Product extends AbstractDb
{
    /**
     * Product/Category relation table name
     */
    const TABLE_NAME = 'catalog_url_rewrite_product_category';

    /**
     * Chunk for mass insert
     */
    const CHUNK_SIZE = 100;

    /**
     * Primary key auto increment flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'url_rewrite_id');
    }

    /**
     * @param array $insertData
     * @return int
     * @since 2.0.0
     */
    public function saveMultiple(array $insertData)
    {
        $connection = $this->getConnection();
        if (sizeof($insertData) <= self::CHUNK_SIZE) {
            return $connection->insertMultiple($this->getTable(self::TABLE_NAME), $insertData);
        }
        $data = array_chunk($insertData, self::CHUNK_SIZE);
        $totalCount = 0;
        foreach ($data as $insertData) {
            $totalCount += $connection->insertMultiple($this->getTable(self::TABLE_NAME), $insertData);
        }
        return $totalCount;
    }

    /**
     * Removes data by primary key
     *
     * @param array $removeData
     * @return int
     * @since 2.0.0
     */
    public function removeMultiple(array $removeData)
    {
        return $this->getConnection()->delete(
            $this->getTable(self::TABLE_NAME),
            ['url_rewrite_id in (?)' => $removeData]
        );
    }

    /**
     * Removes multiple entities from url_rewrite table using entities from catalog_url_rewrite_product_category
     * Example: $filter = ['category_id' => [1, 2, 3], 'product_id' => [1, 2, 3]]
     *
     * @param array $filter
     * @return int
     * @since 2.2.0
     */
    public function removeMultipleByProductCategory(array $filter)
    {
        return $this->getConnection()->delete(
            $this->getTable(self::TABLE_NAME),
            ['url_rewrite_id in (?)' => $this->prepareSelect($filter)]
        );
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param array $data
     * @return \Magento\Framework\DB\Select
     * @since 2.2.0
     */
    private function prepareSelect($data)
    {
        $select = $this->getConnection()->select();
        $select->from($this->getTable(DbStorage::TABLE_NAME), 'url_rewrite_id');

        foreach ($data as $column => $value) {
            $select->where($this->getConnection()->quoteIdentifier($column) . ' IN (?)', $value);
        }
        return $select;
    }
}
