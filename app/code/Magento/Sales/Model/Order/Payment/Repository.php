<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Payment;


use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderPaymentRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Metadata;
use Magento\Sales\Api\Data\OrderPaymentSearchResultInterfaceFactory as SearchResultFactory;

/**
 * Class Repository
 */
class Repository implements OrderPaymentRepositoryInterface
{
    /**
     * Magento\Sales\Model\Order\Payment\Transaction[]
     *
     * @var array
     */
    private $registry = [];

    /**
     * @var Metadata
     */
    protected $metaData;

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @param Metadata $metaData
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(Metadata $metaData, SearchResultFactory $searchResultFactory)
    {
        $this->metaData = $metaData;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * Lists order payments that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteria $searchCriteria The search criteria.
     * @return \Magento\Sales\Api\Data\OrderPaymentSearchResultInterface Order payment search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $searchCriteria)
    {
        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collection */
        $collection = $this->searchResultFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        return $collection;
    }

    /**
     * Loads a specified order payment.
     *
     * @param int $id The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException(__('ID required'));
        }
        if (!isset($this->registry[$id])) {
            $entity = $this->metaData->getNewInstance()->load($id);
            if (!$entity->getId()) {
                throw new NoSuchEntityException(__('Requested entity doesn\'t exist'));
            }
            $this->registry[$id] = $entity;
        }
        return $this->registry[$id];
    }

    /**
     * Deletes a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->delete($entity);
        return true;
    }

    /**
     * Performs persist operations for a specified order payment.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $entity The order payment ID.
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Order payment interface.
     */
    public function save(\Magento\Sales\Api\Data\OrderPaymentInterface $entity)
    {
        $this->metaData->getMapper()->save($entity);
        return $entity;
    }

    /**
     * Creates new Order Payment instance.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface Transaction interface.
     */
    public function create()
    {
        return $this->metaData->getNewInstance();
    }
}
