<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\Api;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;

/**
 * Class SearchResult
 * Generic Search Result
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class SearchResult extends AbstractCollection implements Api\Search\SearchResultInterface
{
    /**
     * @var Api\Search\AggregationInterface
     * @since 2.0.0
     */
    protected $aggregations;

    /**
     * @var Api\Search\SearchCriteriaInterface
     * @since 2.0.0
     */
    protected $searchCriteria;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $totalCount;

    /**
     * @var string class name of document
     * @since 2.1.0
     */
    protected $document = Document::class;

    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var string
     * @since 2.2.0
     */
    private $identifierName;

    /**
     * SearchResult constructor.
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param null|string $resourceModel
     * @param null|string $identifierName
     * @param null|string $connectionName
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->_init($this->document, $resourceModel);
        $this->setMainTable(true);
        if ($connectionName) {
            $connection  = $this->getResourceConnection()->getConnectionByName($connectionName);
        } else {
            $connection = $this->getResourceConnection()->getConnection();
        }
        $this->setMainTable($this->getResourceConnection()->getTableName($mainTable));
        $this->identifierName = $identifierName;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            null
        );
        $this->_setIdFieldName($this->getIdentifierName());
    }

    /**
     * @deprecated 2.2.0
     * @return ResourceConnection
     * @since 2.2.0
     */
    private function getResourceConnection()
    {
        if ($this->resourceConnection == null) {
            $this->resourceConnection = ObjectManager::getInstance()->get(ResourceConnection::class);
        }
        return $this->resourceConnection;
    }

    /**
     * @return \Magento\Framework\Api\Search\AggregationInterface
     * @since 2.0.0
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     *  Get resource instance
     *
     * @return ResourceConnection|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @since 2.2.0
     */
    public function getResource()
    {
        if ($this->_resourceModel) {
            return parent::getResource();
        }
        return $this->getResourceConnection();
    }

    /**
     * @param \Magento\Framework\Api\Search\AggregationInterface $aggregations
     * @return void
     * @since 2.0.0
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @return \Magento\Framework\Api\Search\SearchCriteriaInterface|null
     * @since 2.0.0
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getTotalCount()
    {
        if (!$this->totalCount) {
            $this->totalCount = $this->getSize();
        }
        return $this->totalCount;
    }

    /**
     * @param int $totalCount
     * @return $this
     * @since 2.0.0
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    /**
     * Set items list.
     *
     * @param Document[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        if ($items) {
            foreach ($items as $item) {
                $this->addItem($item);
            }
            unset($this->totalCount);
        }
        return $this;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     * @since 2.2.0
     */
    public function getTable($table)
    {
        return $this->getResourceConnection()->getTableName($table);
    }

    /**
     * Get Identifier name
     *
     * @return string|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.2.0
     */
    private function getIdentifierName()
    {
        if ($this->_resourceModel) {
            return $this->getResource()->getIdFieldName();
        } elseif ($this->identifierName !== null) {
            return $this->identifierName;
        } else {
            return $this->getResourceConnection()->getConnection()->getAutoIncrementField($this->getMainTable());
        }
    }

    /**
     * Initialize initial fields to select like id field
     *
     * @return $this
     * @since 2.2.0
     */
    protected function _initInitialFieldsToSelect()
    {
        $idFieldName = $this->getIdentifierName();
        if ($idFieldName) {
            $this->_initialFieldsToSelect[] = $idFieldName;
        }
        return $this;
    }

    /**
     * Retrieve all ids for collection
     *
     * @return array
     * @since 2.2.0
     */
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns($this->getIdentifierName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
