<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class SalesOrderAfterCancelObserver
 *
 * @package Magento\SalesRule\Observer
 */
class SalesOrderAfterCancelObserver implements ObserverInterface
{
    /**
     * @var Coupon
     */
    public $coupon;

    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Usage
     */
    protected $couponUsage;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SalesOrderAfterCancelObserver constructor.
     *
     * @param \Magento\SalesRule\Model\Coupon                     $coupon
     * @param \Magento\SalesRule\Api\CouponRepositoryInterface    $couponRepository
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory       $customerFactory
     * @param \Magento\Framework\App\ResourceConnection           $resourceConnection
     * @param \Psr\Log\LoggerInterface                            $logger
     */
    public function __construct(
        Coupon $coupon,
        CouponRepositoryInterface $couponRepository,
        Usage $couponUsage,
        CustomerFactory $customerFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->coupon = $coupon;
        $this->couponRepository = $couponRepository;
        $this->couponUsage = $couponUsage;
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getOrder();
        $customerId = $order->getCustomerId();
        $couponCode = $order->getCouponCode();
        if ($customerId !== null && !empty($couponCode)) {
            /** @var Coupon $coupon */
            $coupon = $this->coupon->loadByCode($couponCode);
            if ($coupon->getCouponId()) {
                $this->restoreCoupon($coupon, $customerId);
            }
        }

        return $this;
    }

    /**
     * Restore coupon
     *
     * @param Coupon $coupon
     * @param int    $customerId
     */
    protected function restoreCoupon($coupon, $customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $couponId = $coupon->getCouponId();
            $ruleId = $coupon->getRuleId();
            $coupon->setTimesUsed($coupon->getTimesUsed() - 1);
            $this->couponRepository->save($coupon);

            $this->couponUsage->updateCustomerCouponTimesUsed($customerId, $couponId, -1);

            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() - 1);
                $ruleCustomer->save();
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
        }
    }
}