<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Map;

use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\TemporaryTableService;
use \Magento\Framework\DB\Select;

/**
 * Map that holds data for category url rewrites entity
 */
class DataCategoryUrlRewriteMap implements DataMapInterface
{
    const ENTITY_TYPE = 'category';

    /** @var string[] */
    private $tableNames = [];

    /** @var UrlRewrite */
    private $urlRewritePlaceholder;

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var ResourceConnection */
    private $connection;

    /** @var TemporaryTableService */
    private $temporaryTableService;

    /**
     * @param ResourceConnection $connection
     * @param DataMapPoolInterface $dataMapPool,
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param TemporaryTableService $temporaryTableService,
     */
    public function __construct(
        ResourceConnection $connection,
        DataMapPoolInterface $dataMapPool,
        UrlRewriteFactory $urlRewriteFactory,
        TemporaryTableService $temporaryTableService
    ) {
        $this->connection = $connection;
        $this->dataMapPool = $dataMapPool;
        $this->temporaryTableService = $temporaryTableService;
        $this->urlRewritePlaceholder = $urlRewriteFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllData($categoryId)
    {
        if (empty($this->tableNames[$categoryId])) {
            $this->tableNames[$categoryId] = $this->generateData($categoryId);
        }
        return $this->tableNames[$categoryId];
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
                ['e.*', 'hash_key' => new \Zend_Db_Expr('CONCAT(e.store_id,\'_\', e.entity_id)')]
            )
            ->where('entity_type = ?', self::ENTITY_TYPE)
            ->where(
                $urlRewritesConnection->prepareSqlCondition(
                    'entity_id',
                    [
                        'in' => array_merge(
                            $this->dataMapPool->getDataMap(DataCategoryUsedInProductsMap::class, $categoryId)
                                ->getAllData($categoryId),
                            $this->dataMapPool->getDataMap(DataCategoryMap::class, $categoryId)
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
    public function resetData($categoryId)
    {
        $this->dataMapPool->resetDataMap(DataCategoryUsedInProductsMap::class, $categoryId);
        $this->dataMapPool->resetDataMap(DataCategoryMap::class, $categoryId);
        $this->temporaryTableService->dropTable($this->tableNames[$categoryId]);
        unset($this->tableNames[$categoryId]);
        if (empty($this->tableNames)) {
            $this->tableNames = [];
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
        $this->getAllData($categoryId);
        $urlRewritesConnection = $this->connection->getConnection();
        $select = $urlRewritesConnection->select()
            ->from(['e' => $this->tableNames[$categoryId]])
            ->where('hash_key = ?', $key);

        return $urlRewritesConnection->fetchAll($select);
    }
}
