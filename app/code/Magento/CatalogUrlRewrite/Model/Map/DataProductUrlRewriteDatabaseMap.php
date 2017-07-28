<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;
use Magento\Framework\DB\Select;
use Magento\UrlRewrite\Model\MergeDataProvider;

/**
 * Map that holds data for category url rewrites entity
 * @since 2.2.0
 */
class DataProductUrlRewriteDatabaseMap implements DatabaseMapInterface
{
    const ENTITY_TYPE = 'product';

    /**
     * @var string[]
     * @since 2.2.0
     */
    private $createdTableAdapters = [];

    /**
     * @var \Magento\CatalogUrlRewrite\Model\Map\HashMapPool
     * @since 2.2.0
     */
    private $hashMapPool;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $connection;

    /**
     * @var \Magento\Framework\DB\TemporaryTableService
     * @since 2.2.0
     */
    private $temporaryTableService;

    /**
     * @param ResourceConnection $connection
     * @param HashMapPool $hashMapPool,
     * @param TemporaryTableService $temporaryTableService
     * @since 2.2.0
     */
    public function __construct(
        ResourceConnection $connection,
        HashMapPool $hashMapPool,
        TemporaryTableService $temporaryTableService
    ) {
        $this->connection = $connection;
        $this->hashMapPool = $hashMapPool;
        $this->temporaryTableService = $temporaryTableService;
    }

    /**
     * Generates data from categoryId and stores it into a temporary table
     *
     * @param int $categoryId
     * @return void
     * @since 2.2.0
     */
    private function generateTableAdapter($categoryId)
    {
        if (!isset($this->createdTableAdapters[$categoryId])) {
            $this->createdTableAdapters[$categoryId] = $this->generateData($categoryId);
        }
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getData($categoryId, $key)
    {
        $this->generateTableAdapter($categoryId);
        $urlRewritesConnection = $this->connection->getConnection();
        $select = $urlRewritesConnection->select()
            ->from(['e' => $this->createdTableAdapters[$categoryId]])
            ->where('hash_key = ?', $key);
        return $urlRewritesConnection->fetchAll($select);
    }

    /**
     * Queries the database for all category url rewrites that are affected by the category identified by $categoryId
     * It returns the name of the temporary table where the resulting data is stored
     *
     * @param int $categoryId
     * @return string
     * @since 2.2.0
     */
    private function generateData($categoryId)
    {
        $urlRewritesConnection = $this->connection->getConnection();
        $select = $urlRewritesConnection->select()
            ->from(
                ['e' => $this->connection->getTableName('url_rewrite')],
                ['e.*', 'hash_key' => new \Zend_Db_Expr(
                    "CONCAT(e.store_id,'" . MergeDataProvider::SEPARATOR . "', e.entity_id)"
                )
                ]
            )
            ->where('entity_type = ?', self::ENTITY_TYPE)
            ->where(
                $urlRewritesConnection->prepareSqlCondition(
                    'entity_id',
                    [
                        'in' => $this->hashMapPool->getDataMap(DataProductHashMap::class, $categoryId)
                            ->getAllData($categoryId)
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
     * @since 2.2.0
     */
    public function destroyTableAdapter($categoryId)
    {
        $this->hashMapPool->resetMap(DataProductHashMap::class, $categoryId);
        if (isset($this->createdTableAdapters[$categoryId])) {
            $this->temporaryTableService->dropTable($this->createdTableAdapters[$categoryId]);
            unset($this->createdTableAdapters[$categoryId]);
        }
    }
}
