<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\UrlRewrite\Model\Storage\DbStorage;

/**
 * Product Resource Class
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
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'url_rewrite_id');
    }

    /**
     * Save multiple data
     *
     * @param array $insertData
     * @return int
     */
    public function saveMultiple(array $insertData)
    {
        $connection = $this->getConnection();
        if (count($insertData) <= self::CHUNK_SIZE) {
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
     */
    public function removeMultiple(array $removeData)
    {
        return $this->getConnection()->delete(
            $this->getTable(self::TABLE_NAME),
            ['url_rewrite_id in (?)' => $removeData]
        );
    }

    /**
     * Removes multiple data by filter
     *
     * Removes multiple entities from url_rewrite table
     * using entities from catalog_url_rewrite_product_category
     * Example: $filter = ['category_id' => [1, 2, 3], 'product_id' => [1, 2, 3]]
     *
     * @param array $filter
     * @return int
     */
    public function removeMultipleByProductCategory(array $filter)
    {
        return $this->getConnection()->deleteFromSelect($this->prepareSelect($filter), self::TABLE_NAME);
    }

    /**
     * Prepare select statement for specific filter
     *
     * @param array $data
     * @return \Magento\Framework\DB\Select
     */
    private function prepareSelect($data)
    {
        $select = $this->getConnection()->select();
        $select->from(DbStorage::TABLE_NAME);
        $select->join(
            self::TABLE_NAME,
            DbStorage::TABLE_NAME . '.url_rewrite_id = ' . self::TABLE_NAME . '.url_rewrite_id'
        );
        foreach ($data as $column => $value) {
            $select->where(DbStorage::TABLE_NAME . '.' . $column . ' IN (?)', $value);
        }
        return $select;
    }
}
