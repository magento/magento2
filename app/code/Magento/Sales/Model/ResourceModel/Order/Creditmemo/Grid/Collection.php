<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * Initialize dependencies.
     *
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'sales_creditmemo_grid',
        $resourceModel = \Magento\Sales\Model\ResourceModel\Order\Creditmemo::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * @inheritDoc
     */
    protected function _translateCondition($field, $condition)
    {
        if ($field !== 'order_currency_code'
            && !isset($this->_map['fields'][$field])
        ) {
            $this->_map['fields'][$field] = 'main_table.' . $field;
        }

        return parent::_translateCondition($field, $condition);
    }

    /**
     * @inheritDoc
     */
    protected function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['cgf' => $this->getTable('sales_order_grid')],
            'main_table.order_id = cgf.entity_id',
            [
                'order_currency_code' => 'order_currency_code',
            ]
        );
    }
}
