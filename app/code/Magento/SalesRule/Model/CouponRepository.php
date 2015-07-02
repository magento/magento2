<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Model;

use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\SalesRule\Model\Resource\Rule\Collection;

/**
 * Coupon CRUD class
 *
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
     * @var \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory
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

    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
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
        $this->resourceModel->save($coupon);
        return $coupon;
    }

    /**
     * Get coupon by coupon id.
     *
     * @param int $id
     * @return \Magento\SalesRule\Api\Data\CouponInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If $id is not found
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id)
    {
        $coupon = $this->couponFactory->create()
            ->load($id);

        if (!$coupon->getId()) {
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
        // TODO: Implement getList() method.
    }

    /**
     * Delete coupon by coupon id.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        /** @var \Magento\SalesRule\Model\Coupon $coupon */
        $coupon = $this->couponFactory->create()
            ->load($id);

        if (!$coupon->getCouponId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException();
        }

        $this->resourceModel->delete($coupon);
        return true;
    }
}
