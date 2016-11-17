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

/**
 * Map that holds data for category url rewrites entity
 */
class DataCategoryUrlRewriteMap implements DataMapInterface
{
    const ENTITY_TYPE = 'category';

    /** @var string[] */
    private $data = [];

    /** @var UrlRewrite */
    private $urlRewritePlaceholder;

    /** @var DataMapPoolInterface */
    private $dataMapPool;

    /** @var ResourceConnection */
    private $connection;

    /** @var UrlRewriteFactory */
    private $urlRewriteFactory;

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
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->temporaryTableService = $temporaryTableService;
        $this->urlRewritePlaceholder = $this->urlRewriteFactory->create();
    }

    /**
     * Gets all data from a map identified by a category Id
     *
     * @param int $categoryId
     * @return array
     */
    public function getData($categoryId)
    {
        if (empty($this->data[$categoryId])) {
            $this->data[$categoryId] = [$this->queryData($categoryId)];
        }
        return $this->data[$categoryId];
    }

    /**
     * Queries the database and returns the name of the temporary table where data is stored
     *
     * @param int $categoryId
     * @return string
     */
    private function queryData($categoryId)
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
                                ->getData($categoryId),
                            $this->dataMapPool->getDataMap(DataCategoryMap::class, $categoryId)
                                ->getData($categoryId)
                        )
                    ]
                )
            );
        $mapName = $this->temporaryTableService->createTemporaryTable(
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
     * Resets current map and it's dependencies
     *
     * @param int $categoryId
     * @return $this
     */
    public function resetData($categoryId)
    {
        $this->dataMapPool->resetDataMap(DataCategoryUsedInProductsMap::class, $categoryId);
        $this->dataMapPool->resetDataMap(DataCategoryMap::class, $categoryId);
        foreach ($this->data as $tableName) {
            $this->temporaryTableService->dropTable(reset($tableName));
        }
        unset($this->data);
        $this->data = [];
        return $this;
    }
}
