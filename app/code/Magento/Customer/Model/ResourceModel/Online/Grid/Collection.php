<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\ResourceModel\Online\Grid;

use Magento\Customer\Model\Visitor;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

/**
 * Flat customer online grid collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends SearchResult
{
    /**
     * Value of seconds in one minute
     */
    public const SECONDS_IN_MINUTE = 60;

    /**
     * @var DateTime
     */
    protected DateTime $date;

    /**
     * @var Visitor
     */
    protected Visitor $visitorModel;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param Visitor $visitorModel
     * @param DateTime $date
     * @throws LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel,
        Visitor $visitorModel,
        DateTime $date
    ) {
        $this->date = $date;
        $this->visitorModel = $visitorModel;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Init collection select
     *
     * @return $this
     */
    protected function _initSelect(): Collection
    {
        parent::_initSelect();
        $connection = $this->getConnection();
        $lastDate = $this->date->gmtTimestamp() - $this->visitorModel->getOnlineInterval() * self::SECONDS_IN_MINUTE;
        $this->getSelect()->joinLeft(
            ['customer' => $this->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id',
            ['email', 'firstname', 'lastname']
        )->where(
            'main_table.last_visit_at >= ?',
            $connection->formatDate($lastDate)
        );
        $this->addFilterToMap('customer_id', 'main_table.customer_id');
        $expression = $connection->getCheckSql(
            'main_table.customer_id IS NOT NULL AND main_table.customer_id != 0',
            $connection->quote(Visitor::VISITOR_TYPE_CUSTOMER),
            $connection->quote(Visitor::VISITOR_TYPE_VISITOR)
        );
        $this->getSelect()->columns(['visitor_type' => $expression]);
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return Collection
     */
    public function addFieldToFilter($field, $condition = null): Collection
    {
        if ($field == 'visitor_type') {
            $field = 'customer_id';
            if (is_array($condition) && isset($condition['eq'])) {
                $condition = $condition['eq'] == Visitor::VISITOR_TYPE_CUSTOMER ? ['gt' => 0] : ['null' => true];
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
