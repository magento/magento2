<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

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
     * @param array $insertData
     * @return int
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
}
