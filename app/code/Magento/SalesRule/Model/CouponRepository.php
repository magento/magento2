<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model;

use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SortOrder;
use Magento\SalesRule\Model\ResourceModel\Coupon\Collection;

/**
 * Coupon CRUD class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponRepository implements \Magento\SalesRule\Api\CouponRepositoryInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Spi\CouponResourceInterface
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /** @var  CollectionProcessorInterface */
    private $collectionProcessor;

    /**
     * CouponRepository constructor.
     * @param CouponFactory $couponFactory
     * @param RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory $searchResultFactory
     * @param ResourceModel\Coupon\CollectionFactory $collectionFactory
     * @param Spi\CouponResourceInterface $resourceModel
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Api\Data\CouponSearchResultInterfaceFactory $searchResultFactory,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save coupon.
     *
     * @param \Magento\SalesRule\Api\Data\CouponInterface $coupon
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a coupon ID is sent but the coupon does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Magento\SalesRule\Api\Data\CouponInterface $coupon)
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
                throw \Magento\Framework\Exception\NoSuchEntityException::singleField('rule_id', $coupon->getRuleId());
            }
            if ($rule->getCouponType() == $rule::COUPON_TYPE_NO_COUPON) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Specified rule does not allow coupons')
                );
            } elseif ($rule->getUseAutoGeneration() && $coupon->getType() == $coupon::TYPE_MANUAL) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Specified rule only allows auto generated coupons')
                );
            } elseif (!$rule->getUseAutoGeneration() && $coupon->getType() == $coupon::TYPE_GENERATED) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Specified rule does not allow auto generated coupons')
                );
            }
            $coupon->setExpirationDate($rule->getToDate());
            $coupon->setUsageLimit($rule->getUsesPerCoupon());
            $coupon->setUsagePerCustomer($rule->getUsesPerCustomer());
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
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
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $couponId is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($couponId)
    {
        $coupon = $this->couponFactory->create()->load($couponId);

        if (!$coupon->getCouponId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }
        return $coupon;
    }

    /**
     * Retrieve coupon.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\SalesRule\Api\Data\CouponSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\SalesRule\Model\ResourceModel\Coupon\Collection $collection */
        $collection = $this->collectionFactory->create();
        $couponInterfaceName = \Magento\SalesRule\Api\Data\CouponInterface::class;
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($couponId)
    {
        /** @var \Magento\SalesRule\Model\Coupon $coupon */
        $coupon = $this->couponFactory->create()
            ->load($couponId);

        if (!$coupon->getCouponId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }

        $this->resourceModel->delete($coupon);
        return true;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param Collection $collection
     * @deprecated
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
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
     * @deprecated
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
