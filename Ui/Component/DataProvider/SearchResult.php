<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\AsynchronousOperations\Model\StatusMapper;

/**
 * Class SearchResult
 */
class SearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @var StatusMapper
     */
    private $statusMapper;

    /**
     * @var array|int
     */
    private $operationStatus;

    /**
     * SearchResult constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param UserContextInterface $userContextInterface
     * @param StatusMapper $statusMapper
     * @param string $mainTable
     * @param null $resourceModel
     * @param string $identifierName
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        UserContextInterface $userContextInterface,
        StatusMapper $statusMapper,
        $mainTable = 'magento_bulk',
        $resourceModel = null,
        $identifierName = 'uuid'
    ) {
        $this->userContext = $userContextInterface;
        $this->statusMapper = $statusMapper;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getMainTable()],
            [
                '*',
                'status' => $this->getCalculatedStatusSql()
            ]
        )->where(
            'user_id=?',
            $this->userContext->getUserId()
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        /** @var BulkSummaryInterface $item */
        foreach ($this->getItems() as $item) {
            $item->setStatus($this->statusMapper->operationStatusToBulkSummaryStatus($item->getStatus()));
        }
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'status') {
            if (is_array($condition)) {
                foreach ($condition as $value) {
                    $this->operationStatus = $this->statusMapper->bulkSummaryStatusToOperationStatus($value);
                    if (is_array($this->operationStatus)) {
                        foreach ($this->operationStatus as $statusValue) {
                            $this->getSelect()->orHaving('status = ?', $statusValue);
                        }
                        continue;
                    }
                    $this->getSelect()->having('status = ?', $this->operationStatus);
                }

            }
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->columns(['status' => $this->getCalculatedStatusSql()]);
        //add grouping by status if filtering by status was executed
        if (isset($this->operationStatus)) {
            $select->group('status');
        }
        return $select;
    }

    /**
     * @return \Zend_Db_Expr
     */
    private function getCalculatedStatusSql()
    {
        $operationTableName = $this->getTable('magento_operation');
        return new \Zend_Db_Expr(
            '(IF(
                (SELECT count(*)
                    FROM ' . $operationTableName . '
                    WHERE bulk_uuid = main_table.uuid
                    AND status != ' . OperationInterface::STATUS_TYPE_OPEN . '
                ) = 0,
                ' . 0 . ',
                (SELECT MAX(status) FROM ' . $operationTableName . ' WHERE bulk_uuid = main_table.uuid)
            ))'
        );
    }
}
