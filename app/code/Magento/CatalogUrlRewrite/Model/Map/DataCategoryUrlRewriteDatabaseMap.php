<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;
use Magento\Framework\DB\Select;
use Magento\UrlRewrite\Model\UrlRewritesSet;

/**
 * Map that holds data for category url rewrites entity
 */
class DataCategoryUrlRewriteDatabaseMap implements DatabaseMapInterface
{
    const ENTITY_TYPE = 'category';

    /** @var string[] */
    private $createdTableAdapters = [];

    /** @var HashMapPool */
    private $dataMapPool;

    /** @var ResourceConnection */
    private $connection;

    /** @var TemporaryTableService */
    private $temporaryTableService;

    /**
     * @param ResourceConnection $connection
     * @param HashMapPool $dataMapPool,
     * @param TemporaryTableService $temporaryTableService
     */
    public function __construct(
        ResourceConnection $connection,
        HashMapPool $dataMapPool,
        TemporaryTableService $temporaryTableService
    ) {
        $this->connection = $connection;
        $this->dataMapPool = $dataMapPool;
        $this->temporaryTableService = $temporaryTableService;
    }

    /**
     * Generates data from categoryId and stores it into a temporary table
     *
     * @param $categoryId
     * @return void
     */
    private function generateTableAdapter($categoryId)
    {
        if (!isset($this->createdTableAdapters[$categoryId])) {
            $this->createdTableAdapters[$categoryId] = $this->generateData($categoryId);
        }
    }

    /**
     * Queries the database and returns the name of the temporary table where data is stored
     *
     * @param int $categoryId
     * @return string
     */
    private function generateData($categoryId)
    {
        $urlRewritesConnection = $this->connection->getConnection();
        $select = $urlRewritesConnection->select()
            ->from(
                ['e' => $this->connection->getTableName('url_rewrite')],
                ['e.*', 'hash_key' => new \Zend_Db_Expr(
                    "CONCAT(e.store_id,'" . UrlRewritesSet::SEPARATOR . "', e.entity_id)"
                )
                ]
            )
            ->where('entity_type = ?', self::ENTITY_TYPE)
            ->where(
                $urlRewritesConnection->prepareSqlCondition(
                    'entity_id',
                    [
                        'in' => array_merge(
                            $this->dataMapPool->getDataMap(DataCategoryUsedInProductsHashMap::class, $categoryId)
                                ->getAllData($categoryId),
                            $this->dataMapPool->getDataMap(DataCategoryHashMap::class, $categoryId)
                                ->getAllData($categoryId)
                        )
                    ]
                )
            );
        $mapName = $this->temporaryTableService->createFromSelect(
            $select,
            $this->connection->getConnection(),
            [
                'PRIMARY' => ['url_rewrite_id'],
                'HASHKEY_ENTITY_STORE' => ['hash_key'],
                'ENTITY_STORE' => ['entity_id', 'store_id']
            ]
        );
        return $mapName;
    }

    /**
     * {@inheritdoc}
     */
    public function destroyTableAdapter($categoryId)
    {
        $this->dataMapPool->resetMap(DataCategoryUsedInProductsHashMap::class, $categoryId);
        $this->dataMapPool->resetMap(DataCategoryHashMap::class, $categoryId);
        if (isset($this->createdTableAdapters[$categoryId])) {
            $this->temporaryTableService->dropTable($this->createdTableAdapters[$categoryId]);
            unset($this->createdTableAdapters[$categoryId]);
        }
    }

    /**
     * Gets data by criteria from a map identified by a category Id
     *
     * @param int $categoryId
     * @param string $key
     * @return array
     */
    public function getData($categoryId, $key)
    {
        $this->generateTableAdapter($categoryId);
        $urlRewritesConnection = $this->connection->getConnection();
        $select = $urlRewritesConnection->select()->from(['e' => $this->createdTableAdapters[$categoryId]]);
        if (strlen($key) > 0) {
            $select->where('hash_key = ?', $key);
        }

        return $urlRewritesConnection->fetchAll($select);
    }
}
