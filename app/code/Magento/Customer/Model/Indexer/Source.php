<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\ResourceModel\Customer\Indexer\Collection;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Traversable;

/**
 * Customers data batch generator for customer_grid indexer
 * @since 2.2.0
 */
class Source implements \IteratorAggregate, \Countable, SourceProviderInterface
{
    /**
     * @var Collection
     * @since 2.2.0
     */
    private $customerCollection;

    /**
     * @var int
     * @since 2.2.0
     */
    private $batchSize;

    /**
     * @param \Magento\Customer\Model\ResourceModel\Customer\Indexer\CollectionFactory $collection
     * @param int $batchSize
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\Indexer\CollectionFactory $collectionFactory,
        $batchSize = 10000
    ) {
        $this->customerCollection = $collectionFactory->create();
        $this->batchSize = $batchSize;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getMainTable()
    {
        return $this->customerCollection->getMainTable();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getIdFieldName()
    {
        return $this->customerCollection->getIdFieldName();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function addFieldToSelect($fieldName, $alias = null)
    {
        $this->customerCollection->addFieldToSelect($fieldName, $alias);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSelect()
    {
        return $this->customerCollection->getSelect();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $this->customerCollection->addFieldToFilter($attribute, $condition);
        return $this;
    }

    /**
     * @return int
     * @since 2.2.0
     */
    public function count()
    {
        return $this->customerCollection->getSize();
    }

    /**
     * Retrieve an iterator
     *
     * @return Traversable
     * @since 2.2.0
     */
    public function getIterator()
    {
        $this->customerCollection->setPageSize($this->batchSize);
        $lastPage = $this->customerCollection->getLastPageNumber();
        $pageNumber = 0;
        do {
            $this->customerCollection->clear();
            $this->customerCollection->setCurPage($pageNumber);
            foreach ($this->customerCollection->getItems() as $key => $value) {
                yield $key => $value;
            }
            $pageNumber++;
        } while ($pageNumber <= $lastPage);
    }
}
