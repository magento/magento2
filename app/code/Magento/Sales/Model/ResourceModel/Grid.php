<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\Grid\LastUpdateTimeCache;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;

/**
 * Sales order grid resource model.
 */
class Grid extends AbstractGrid
{
    /**
     * @var string
     */
    protected $gridTableName;

    /**
     * @var string
     */
    protected $mainTableName;

    /**
     * @var string
     */
    protected $orderIdField;

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var NotSyncedDataProviderInterface
     */
    private $notSyncedDataProvider;

    /**
     * @var LastUpdateTimeCache
     */
    private $lastUpdateTimeCache;

    /**
     * Order grid rows batch size
     */
    const BATCH_SIZE = 100;

    /**
     * @param Context $context
     * @param string $mainTableName
     * @param string $gridTableName
     * @param string $orderIdField
     * @param array $joins
     * @param array $columns
     * @param string $connectionName
     * @param NotSyncedDataProviderInterface|null $notSyncedDataProvider
     * @param LastUpdateTimeCache|null $lastUpdateTimeCache
     */
    public function __construct(
        Context $context,
        $mainTableName,
        $gridTableName,
        $orderIdField,
        array $joins = [],
        array $columns = [],
        $connectionName = null,
        ?NotSyncedDataProviderInterface $notSyncedDataProvider = null,
        ?LastUpdateTimeCache $lastUpdateTimeCache = null
    ) {
        $this->mainTableName = $mainTableName;
        $this->gridTableName = $gridTableName;
        $this->orderIdField = $orderIdField;
        $this->joins = $joins;
        $this->columns = $columns;
        $this->notSyncedDataProvider = $notSyncedDataProvider ??
            ObjectManager::getInstance()->get(NotSyncedDataProviderInterface::class);
        $this->lastUpdateTimeCache = $lastUpdateTimeCache ??
            ObjectManager::getInstance()->get(LastUpdateTimeCache::class);

        parent::__construct($context, $connectionName);
    }

    /**
     * Adds new orders to the grid.
     *
     * Only orders that correspond to $value and $field parameters will be added.
     *
     * @param int|string $value
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refresh($value, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: $this->mainTableName . '.entity_id') . ' = ?', $value);
        $sql = $this->getConnection()
            ->insertFromSelect(
                $select,
                $this->getTable($this->gridTableName),
                array_keys($this->columns),
                AdapterInterface::INSERT_ON_DUPLICATE
            );

        $this->addCommitCallback(function () use ($sql) {
            $this->getConnection()->query($sql);
        });

        // need for backward compatibility
        return $this->getConnection()->query($sql);
    }

    /**
     * Adds new orders to the grid.
     *
     * Only orders created/updated since the last method call will be added.
     *
     * @return void
     */
    public function refreshBySchedule()
    {
        $lastUpdatedAt = null;
        $notSyncedIds = $this->notSyncedDataProvider->getIds($this->mainTableName, $this->gridTableName);
        foreach (array_chunk($notSyncedIds, self::BATCH_SIZE) as $bunch) {
            $select = $this->getGridOriginSelect()->where($this->mainTableName . '.entity_id IN (?)', $bunch);
            $fetchResult = $this->getConnection()->fetchAll($select);
            $this->getConnection()->insertOnDuplicate(
                $this->getTable($this->gridTableName),
                $fetchResult,
                array_keys($this->columns)
            );

            $timestamps = array_column($fetchResult, 'updated_at');
            if ($timestamps) {
                $lastUpdatedAt = max(max($timestamps), $lastUpdatedAt);
            }
        }

        if ($lastUpdatedAt) {
            $this->lastUpdateTimeCache->save($this->gridTableName, $lastUpdatedAt);
        }
    }

    /**
     * Get order id field.
     *
     * @return string
     */
    public function getOrderIdField()
    {
        return $this->orderIdField;
    }

    /**
     * Returns select object
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function getGridOriginSelect()
    {
        $select = $this->getConnection()->select()
            ->from([$this->mainTableName => $this->getTable($this->mainTableName)], []);
        foreach ($this->joins as $joinName => $data) {
            $select->joinLeft(
                [$joinName => $this->getTable($data['table'])],
                sprintf(
                    '%s.%s = %s.%s',
                    $this->mainTableName,
                    $data['origin_column'],
                    $joinName,
                    $data['target_column']
                ),
                []
            );
        }
        $columns = [];
        foreach ($this->columns as $key => $value) {
            if ($value instanceof \Zend_Db_Expr) {
                $columns[$key] = $value;
            } else {
                $columns[$key] = new \Zend_Db_Expr($value);
            }
        }
        $select->columns($columns);
        return $select;
    }
}
