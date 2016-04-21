<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;


/**
 * Class AbstractSearchResult
 */
abstract class AbstractSearchResult extends AbstractDataObject implements SearchResultInterface
{
    /**
     * Data Interface name
     *
     * @var string
     */
    protected $dataInterface = 'Magento\Framework\DataObject';

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $eventPrefix = '';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $eventObject = '';

    /**
     * Event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager = null;

    /**
     * Total items number
     *
     * @var int
     */
    protected $totalRecords;

    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $isLoaded;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactoryInterface
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $select;

    /**
     * @var \Magento\Framework\Data\SearchResultIteratorFactory
     */
    protected $resultIteratorFactory;

    /**
     * @param \Magento\Framework\DB\QueryInterface $query
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Data\SearchResultIteratorFactory $resultIteratorFactory
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
     */
    abstract protected function init();

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        $this->load();
        return $this->data['items'];
    }

    /**
     * @param \Magento\Framework\DataObject[] $items
     * @return $this
     */
    public function setItems(array $items = null)
    {
        $this->data['items'] = $items;
        return $this;
    }

    /**
     * @return int
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
     */
    public function setTotalCount($totalCount)
    {
        $this->data['total_count'] = $totalCount;
        return $this;
    }

    /**
     * @return \Magento\Framework\Api\CriteriaInterface
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
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * @return \Magento\Framework\Data\SearchResultIterator
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
     */
    public function createDataObject(array $arguments = [])
    {
        return $this->entityFactory->create($this->getDataInterfaceName(), $arguments);
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->query->getIdFieldName();
    }

    /**
     * @return int
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
     */
    protected function isLoaded()
    {
        return $this->isLoaded;
    }

    /**
     * Load data
     *
     * @return void
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
     */
    protected function getDataInterfaceName()
    {
        return $this->dataInterface;
    }

    /**
     * @return \Magento\Framework\DB\QueryInterface
     */
    protected function getQuery()
    {
        return $this->query;
    }
}
