<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\IndexerInterface;

/**
 * Class \Magento\Indexer\Model\Indexer\Collection
 *
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = IndexerInterface::class;

    /**
     * Collection items
     *
     * @var IndexerInterface[]
     * @since 2.2.0
     */
    protected $_items = [];

    /**
     * @var \Magento\Framework\Indexer\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory
     */
    protected $statesFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Magento\Framework\Indexer\ConfigInterface $config
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\Indexer\ConfigInterface $config,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\CollectionFactory $statesFactory
    ) {
        $this->config = $config;
        $this->statesFactory = $statesFactory;
        parent::__construct($entityFactory);
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return \Magento\Indexer\Model\Indexer\Collection
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if (!$this->isLoaded()) {
            $states = $this->statesFactory->create();
            foreach (array_keys($this->config->getIndexers()) as $indexerId) {
                /** @var IndexerInterface $indexer */
                $indexer = $this->getNewEmptyItem();
                $indexer->load($indexerId);
                foreach ($states->getItems() as $state) {
                    /** @var \Magento\Indexer\Model\Indexer\State $state */
                    if ($state->getIndexerId() == $indexerId) {
                        $indexer->setState($state);
                        break;
                    }
                }
                $this->_addItem($indexer);
            }
            $this->_setIsLoaded(true);
        }
        return $this;
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @since 2.2.0
     */
    public function getAllIds()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getId();
        }
        return $ids;
    }

    /**
     * @inheritdoc
     * @return IndexerInterface[]
     * @since 2.2.0
     */
    public function getItems()
    {
        return parent::getItems();
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function getColumnValues($colName)
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function getItemsByColumnValue($column, $value)
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function getItemByColumnValue($column, $value)
    {
        return null;
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function setDataToAll($key, $value = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function setItemObjectClass($className)
    {
        return $this;
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @since 2.2.0
     */
    public function toXml()
    {
        return '';
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function toArray($arrRequiredFields = [])
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @since 2.2.0
     */
    public function toOptionArray()
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @since 2.2.0
     */
    public function toOptionHash()
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    protected function _toOptionArray($valueField = 'id', $labelField = 'name', $additional = [])
    {
        return [];
    }

    /**
     * {@inheritdoc} Prevents handle collection items as DataObject class instances.
     * @deprecated
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'name')
    {
        return [];
    }
}
