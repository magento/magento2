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
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
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
     * @var CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

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
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface      $ruleRepository
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory       $customerFactory
     * @param \Magento\Framework\App\ResourceConnection           $resourceConnection
     * @param \Psr\Log\LoggerInterface                            $logger
     */
    public function __construct(
        Coupon $coupon,
        CouponRepositoryInterface $couponRepository,
        RuleRepositoryInterface $ruleRepository,
        Usage $couponUsage,
        CustomerFactory $customerFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->coupon = $coupon;
        $this->couponRepository = $couponRepository;
        $this->ruleRepository = $ruleRepository;
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
        $order = $observer->getEvent()->getOrder();
        if (!$order || $order->getCustomerId() === null || empty($order->getCouponCode())) {
            return $this;
        }

        $couponCode = $order->getCouponCode();
        /** @var Coupon $coupon */
        $coupon = $this->coupon->loadByCode($couponCode);
        if ($coupon->getCouponId()) {
            $customerId = $order->getCustomerId();
            $this->restoreCoupon($coupon, $customerId);
        }

        return $this;
    }

    /**
     * Restore coupon
     *
     * @param CouponInterface $coupon
     * @param int             $customerId
     *
     * @return bool
     */
    protected function restoreCoupon(CouponInterface $coupon, $customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $ruleId = $coupon->getRuleId();
            $rule = $this->ruleRepository->getById($ruleId);
            $today = date("Y-m-d");
            $ruleToDate = $rule->getToDate();
            if (!$rule->getRuleId() || ($ruleToDate && $ruleToDate < $today)) {
                return false;
            }

            $coupon->setTimesUsed($coupon->getTimesUsed() - 1);
            $this->couponRepository->save($coupon);

            $couponId = $coupon->getCouponId();
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

        return true;
    }
}