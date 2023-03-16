<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model;

use Exception;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Api\Data\CouponSearchResultInterface;
use Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection as CouponCollection;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\Spi\CouponResourceInterface;

/**
 * Coupon CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponRepository implements CouponRepositoryInterface
{
    /**
     * CouponRepository constructor.
     * @param CouponFactory $couponFactory
     * @param RuleFactory $ruleFactory
     * @param CouponSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionFactory $collectionFactory
     * @param Spi\CouponResourceInterface $resourceModel
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        protected readonly CouponFactory $couponFactory,
        protected readonly RuleFactory $ruleFactory,
        protected readonly CouponSearchResultInterfaceFactory $searchResultFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly CouponResourceInterface $resourceModel,
        protected readonly JoinProcessorInterface $extensionAttributesJoinProcessor,
        private ?CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save coupon.
     *
     * @param CouponInterface $coupon
     * @return CouponInterface
     * @throws InputException If there is a problem with the input
     * @throws NoSuchEntityException If a coupon ID is sent but the coupon does not exist
     * @throws LocalizedException
     */
    public function save(CouponInterface $coupon)
    {
        //if coupon id is provided, use the existing coupon and blend in the new data supplied
        $couponId = $coupon->getCouponId();
        if ($couponId) {
            $existingCoupon = $this->getById($couponId);
            $mergedData = array_merge($existingCoupon->getData(), $coupon->getData());
            $coupon->setData($mergedData);
        }

        //blend in specific fields from the rule
        try {
            $rule = $this->ruleFactory->create()->load($coupon->getRuleId());
            if (!$rule->getRuleId()) {
                throw NoSuchEntityException::singleField('rule_id', $coupon->getRuleId());
            }
            if ($rule->getCouponType() == $rule::COUPON_TYPE_NO_COUPON) {
                throw new LocalizedException(
                    __('Specified rule does not allow coupons')
                );
            } elseif ($rule->getUseAutoGeneration() && $coupon->getType() == $coupon::TYPE_MANUAL) {
                throw new LocalizedException(
                    __('Specified rule only allows auto generated coupons')
                );
            } elseif (!$rule->getUseAutoGeneration() && $coupon->getType() == $coupon::TYPE_GENERATED) {
                throw new LocalizedException(
                    __('Specified rule does not allow auto generated coupons')
                );
            }
            $coupon->setUsageLimit($rule->getUsesPerCoupon());
            $coupon->setUsagePerCustomer($rule->getUsesPerCustomer());
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Error occurred when saving coupon: %1', $e->getMessage())
            );
        }

        $this->resourceModel->save($coupon);
        return $coupon;
    }

    /**
     * Get coupon by coupon id.
     *
     * @param int $couponId
     * @return CouponInterface
     * @throws NoSuchEntityException If $couponId is not found
     * @throws LocalizedException
     */
    public function getById($couponId)
    {
        $coupon = $this->couponFactory->create()->load($couponId);

        if (!$coupon->getCouponId()) {
            throw new NoSuchEntityException();
        }
        return $coupon;
    }

    /**
     * Retrieve coupon.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return CouponSearchResultInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var CouponCollection $collection */
        $collection = $this->collectionFactory->create();
        $couponInterfaceName = CouponInterface::class;
        $this->extensionAttributesJoinProcessor->process($collection, $couponInterfaceName);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete coupon by coupon id.
     *
     * @param int $couponId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($couponId)
    {
        /** @var Coupon $coupon */
        $coupon = $this->couponFactory->create()
            ->load($couponId);

        if (!$coupon->getCouponId()) {
            throw new NoSuchEntityException();
        }

        $this->resourceModel->delete($coupon);
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @deprecated 101.0.0
     * @return void
     */
    protected function addFilterGroupToCollection(
        FilterGroup $filterGroup,
        Collection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $fields[] = $filter->getField();
            $conditions[] = [$condition => $filter->getValue()];
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 101.0.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = ObjectManager::getInstance()->get(
                CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
