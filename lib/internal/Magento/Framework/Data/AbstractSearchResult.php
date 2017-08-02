<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

/**
 * Class AbstractSearchResult
 * @since 2.0.0
 */
abstract class AbstractSearchResult extends AbstractDataObject implements SearchResultInterface
{
    /**
     * Data Interface name
     *
     * @var string
     * @since 2.0.0
     */
    protected $dataInterface = \Magento\Framework\DataObject::class;

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     * @since 2.0.0
     */
    protected $eventPrefix = '';

    /**
     * Name of event parameter
     *
     * @var string
     * @since 2.0.0
     */
    protected $eventObject = '';

    /**
     * Event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $eventManager = null;

    /**
     * Total items number
     *
     * @var int
     * @since 2.0.0
     */
    protected $totalRecords;

    /**
     * Loading state flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $isLoaded;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     * @since 2.0.0
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\DB\QueryInterface
     * @since 2.2.0
     */
    protected $query;
    
    /**
     * @var \Magento\Framework\DB\Select
     * @deprecated 2.2.0
     * @since 2.0.0
     */
    protected $select;

    /**
     * @var \Magento\Framework\Data\SearchResultIteratorFactory
     * @since 2.0.0
     */
    protected $resultIteratorFactory;

    /**
     * @param \Magento\Framework\DB\QueryInterface $query
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\DB\QueryInterface $query,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
    ) {
        $this->query = $query;
        $this->eventManager = $eventManager;
        $this->entityFactory = $entityFactory;
        $this->resultIteratorFactory = $resultIteratorFactory;
        $this->init();
    }

    /**
     * Standard query builder initialization
     *
     * @return void
     * @since 2.0.0
     */
    abstract protected function init();

    /**
     * @return \Magento\Framework\DataObject[]
     * @since 2.0.0
     */
    public function getItems()
    {
        $this->load();
        return $this->data['items'];
    }

    /**
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null)
    {
        $this->data['items'] = $items;
        return $this;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getTotalCount()
    {
        if (!isset($this->data['total_count'])) {
            $this->data['total_count'] = $this->query->getSize();
        }
        return $this->data['total_count'];
    }

    /**
     * @param int $totalCount
     * @return $this
     * @since 2.0.0
     */
    public function setTotalCount($totalCount)
    {
        $this->data['total_count'] = $totalCount;
        return $this;
    }

    /**
     * @return \Magento\Framework\Api\CriteriaInterface
     * @since 2.0.0
     */
    public function getSearchCriteria()
    {
        if (!isset($this->data['search_criteria'])) {
            $this->data['search_criteria'] = $this->query->getCriteria();
        }
        return $this->data['search_criteria'];
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\SearchResultIterator
     * @since 2.0.0
     */
    public function createIterator()
    {
        return $this->resultIteratorFactory->create(
            [
                'searchResult' => $this,
                'query' => $this->query,
            ]
        );
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\DataObject|mixed
     * @since 2.0.0
     */
    public function createDataObject(array $arguments = [])
    {
        return $this->entityFactory->create($this->getDataInterfaceName(), $arguments);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getIdFieldName()
    {
        return $this->query->getIdFieldName();
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getSize()
    {
        return $this->query->getSize();
    }

    /**
     * Get collection item identifier
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     * @since 2.0.0
     */
    public function getItemId(\Magento\Framework\DataObject $item)
    {
        $field = $this->query->getIdFieldName();
        if ($field) {
            return $item->getData($field);
        }
        return $item->getId();
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    protected function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Load data
     *
     * @return void
     * @since 2.0.0
     */
    protected function load()
    {
        if (!$this->isLoaded()) {
            $this->beforeLoad();
            $data = $this->query->fetchAll();
            $this->data['items'] = [];
            if (is_array($data)) {
                foreach ($data as $row) {
                    $item = $this->createDataObject(['data' => $row]);
                    $this->addItem($item);
                }
            }
            $this->setIsLoaded(true);
            $this->afterLoad();
        }
    }

    /**
     * Set loading status flag
     *
     * @param bool $flag
     * @return void
     * @since 2.0.0
     */
    protected function setIsLoaded($flag = true)
    {
        $this->isLoaded = $flag;
    }

    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\DataObject $item
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    protected function addItem(\Magento\Framework\DataObject $item)
    {
        $itemId = $this->getItemId($item);
        if ($itemId !== null) {
            if (isset($this->data['items'][$itemId])) {
                throw new \Exception(
                    'Item (' . get_class($item) . ') with the same ID "' . $item->getId() . '" already exists.'
                );
            }
            $this->data['items'][$itemId] = $item;
        } else {
            $this->data['items'][] = $item;
        }
    }

    /**
     * Dispatch "before" load method events
     *
     * @return void
     * @since 2.0.0
     */
    protected function beforeLoad()
    {
        $this->eventManager->dispatch('abstract_search_result_load_before', ['collection' => $this]);
        if ($this->eventPrefix && $this->eventObject) {
            $this->eventManager->dispatch($this->eventPrefix . '_load_before', [$this->eventObject => $this]);
        }
    }

    /**
     * Dispatch "after" load method events
     *
     * @return void
     * @since 2.0.0
     */
    protected function afterLoad()
    {
        $this->eventManager->dispatch('abstract_search_result_load_after', ['collection' => $this]);
        if ($this->eventPrefix && $this->eventObject) {
            $this->eventManager->dispatch($this->eventPrefix . '_load_after', [$this->eventObject => $this]);
        }
    }

    /**
     * Set Data Interface name for collection items
     *
     * @param string $dataInterface
     * @return void
     * @since 2.0.0
     */
    protected function setDataInterfaceName($dataInterface)
    {
        if (is_string($dataInterface)) {
            $this->dataInterface = $dataInterface;
        }
    }

    /**
     * Get Data Interface name for collection items
     *
     * @return string
     * @since 2.0.0
     */
    protected function getDataInterfaceName()
    {
        return $this->dataInterface;
    }

    /**
     * @return \Magento\Framework\DB\QueryInterface
     * @since 2.0.0
     */
    protected function getQuery()
    {
        return $this->query;
    }
}
