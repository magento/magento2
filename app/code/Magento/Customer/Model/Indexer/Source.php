<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Indexer;

use Magento\Customer\Model\ResourceModel\Customer\Indexer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Customer\Indexer\Collection;
use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Traversable;

/**
 * Customers data batch generator for customer_grid indexer
 */
class Source implements \IteratorAggregate, \Countable, SourceProviderInterface
{
    /**
     * @var Collection
     */
    private $customerCollection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param CollectionFactory $collectionFactory
     * @param int $batchSize
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        $batchSize = 10000
    ) {
        $this->customerCollection = $collectionFactory->create();
        $this->batchSize = $batchSize;
    }

    /**
     * @inheritdoc
     */
    public function getMainTable()
    {
        return $this->customerCollection->getMainTable();
    }

    /**
     * @inheritdoc
     */
    public function getIdFieldName()
    {
        return $this->customerCollection->getIdFieldName();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToSelect($fieldName, $alias = null)
    {
        $this->customerCollection->addFieldToSelect($fieldName, $alias);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSelect()
    {
        return $this->customerCollection->getSelect();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        $this->customerCollection->addFieldToFilter($attribute, $condition);
        return $this;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->customerCollection->getSize();
    }

    /**
     * Retrieve an iterator
     *
     * @return Traversable
     */
    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        $this->customerCollection->setPageSize($this->batchSize);
        $lastPage = $this->customerCollection->getLastPageNumber();
        $pageNumber = 1;
        do {
            $this->customerCollection->clear();
            $this->customerCollection->setCurPage($pageNumber);
            foreach ($this->customerCollection->getItems() as $key => $value) {
                yield $key => $value;
            }
            $pageNumber++;
        } while ($pageNumber <= $lastPage);
    }

    /**
     * Joins Attribute
     *
     * @param string $alias alias for the joined attribute
     * @param string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string|null $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     * @param int|null $storeId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @see Collection::joinAttribute()
     */
    public function joinAttribute(
        string $alias,
        $attribute,
        string $bind,
        ?string $filter = null,
        string $joinType = 'inner',
        ?int $storeId = null
    ): void {
        $this->customerCollection->joinAttribute($alias, $attribute, $bind, $filter, $joinType, $storeId);
    }
}
