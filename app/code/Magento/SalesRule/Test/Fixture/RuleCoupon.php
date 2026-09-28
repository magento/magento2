<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\SalesRule\Model\Spi\CouponResourceInterface;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Api\Data\CouponInterface;

class RuleCoupon implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'rule_id' => null,
        'code' => null,
        'usage_limit' => false,
        'usage_per_customer' => false,
        'type' => CouponInterface::TYPE_MANUAL
    ];

    /**
     * @var CouponFactory
     */
    private CouponFactory $couponFactory;

    /**
     * @var CouponResourceInterface
     */
    private CouponResourceInterface $couponRuleResourceModel;

    /**
     * @param CouponResourceInterface $couponRuleResourceModel
     * @param CouponFactory $couponFactory
     */
    public function __construct(
        CouponResourceInterface $couponRuleResourceModel,
        CouponFactory $couponFactory,
    ) {
        $this->couponRuleResourceModel = $couponRuleResourceModel;
        $this->couponFactory = $couponFactory;
    }

    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $coupon = $this->couponFactory->create();
        $coupon->setData($data);
        $this->couponRuleResourceModel->save($coupon);
        return $coupon;
    }

    public function revert(DataObject $data): void
    {
        $coupon = $this->couponFactory->create();
        $this->couponRuleResourceModel->load($coupon, $data->getId());
        if ($coupon->getId()) {
            $this->couponRuleResourceModel->delete($coupon);
        }
    }
}
