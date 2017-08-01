<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;

/**
 * Class Grid
 * @since 2.0.0
 */
class Grid extends AbstractGrid
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $gridTableName;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $mainTableName;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $orderIdField;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $joins;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $columns;

    /**
     * @var NotSyncedDataProviderInterface
     * @since 2.2.0
     */
    private $notSyncedDataProvider;

    /**
     * @param Context $context
     * @param string $mainTableName
     * @param string $gridTableName
     * @param string $orderIdField
     * @param array $joins
     * @param array $columns
     * @param string $connectionName
     * @param NotSyncedDataProviderInterface $notSyncedDataProvider
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        $mainTableName,
        $gridTableName,
        $orderIdField,
        array $joins = [],
        array $columns = [],
        $connectionName = null,
        NotSyncedDataProviderInterface $notSyncedDataProvider = null
    ) {
        $this->mainTableName = $mainTableName;
        $this->gridTableName = $gridTableName;
        $this->orderIdField = $orderIdField;
        $this->joins = $joins;
        $this->columns = $columns;
        $this->notSyncedDataProvider =
            $notSyncedDataProvider ?: ObjectManager::getInstance()->get(NotSyncedDataProviderInterface::class);
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
     * @since 2.0.0
     */
    public function refresh($value, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: $this->mainTableName . '.entity_id') . ' = ?', $value);
        return $this->getConnection()->query(
            $this->getConnection()
                ->insertFromSelect(
                    $select,
                    $this->getTable($this->gridTableName),
                    array_keys($this->columns),
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
        );
    }

    /**
     * Adds new orders to the grid.
     *
     * Only orders created/updated since the last method call will be added.
     *
     * @return \Zend_Db_Statement_Interface
     * @since 2.0.0
     */
    public function refreshBySchedule()
    {
        $select = $this->getGridOriginSelect()
            ->where(
                $this->mainTableName . '.entity_id IN (?)',
                $this->notSyncedDataProvider->getIds($this->mainTableName, $this->gridTableName)
            );

        return $this->getConnection()->query(
            $this->getConnection()
                ->insertFromSelect(
                    $select,
                    $this->getTable($this->gridTableName),
                    array_keys($this->columns),
                    AdapterInterface::INSERT_ON_DUPLICATE
                )
        );
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getOrderIdField()
    {
        return $this->orderIdField;
    }

    /**
     * Returns select object
     *
     * @return \Magento\Framework\DB\Select
     * @since 2.0.0
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
