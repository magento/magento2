<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Order\Payment;

use Magento\Sales\Model\Resource\Order\Payment\Transaction as TransactionResource;
use Magento\Framework\Service\V1\Data\FilterBuilder;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;

/**
 * Repository class for \Magento\Sales\Model\Order\Payment\Transaction
 */
class TransactionRepository
{
    /**
     * transactionFactory
     *
     * @var TransactionFactory
     */
    private $transactionFactory = null;

    /**
     * Collection Factory
     *
     * @var TransactionResource\CollectionFactory
     */
    private $transactionCollectionFactory = null;

    /**
     * Magento\Sales\Model\Order\Payment\Transaction[]
     *
     * @var array
     */
    private $registry = array();

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Repository constructor
     *
     * @param TransactionFactory $transactionFactory
     * @param TransactionResource\CollectionFactory $transactionCollectionFactory
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionResource\CollectionFactory $transactionCollectionFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * load entity
     *
     * @param int $id
     * @return Transaction
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function get($id)
    {
        if (!$id) {
            throw new \Magento\Framework\Exception\InputException('ID required');
        }
        if (!isset($this->registry[$id])) {
            $filter = $this->filterBuilder->setField('transaction_id')->setValue($id)->setConditionType('eq')->create();
            $this->searchCriteriaBuilder->addFilter([$filter]);
            $this->find($this->searchCriteriaBuilder->create());

            if (!isset($this->registry[$id])) {
                throw new \Magento\Framework\Exception\NoSuchEntityException('Requested entity doesn\'t exist');
            }
        }
        return $this->registry[$id];
    }

    /**
     * Register entity
     *
     * @param Transaction $object
     * @return TransactionRepository
     */
    public function register(Transaction $object)
    {
        if ($object->getId() && !isset($this->registry[$object->getId()])) {
            $this->registry[$object->getId()] = $object;
        }
        return $this;
    }

    /**
     * Find entities by criteria
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria  $criteria
     * @return Transaction[]
     */
    public function find(\Magento\Framework\Service\V1\Data\SearchCriteria $criteria)
    {
        /** @var TransactionResource\Collection $collection */
        $collection = $this->transactionCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $collection->addPaymentInformation(['method']);
        $collection->addOrderInformation(['increment_id']);
        foreach ($collection as $object) {
            $this->register($object);
        }
        $objectIds = $collection->getAllIds();
        return array_intersect_key($this->registry, array_flip($objectIds));
    }
}
