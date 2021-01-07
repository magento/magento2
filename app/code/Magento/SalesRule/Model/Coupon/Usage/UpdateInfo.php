<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Coupon\Usage;

use Magento\Framework\DataObject;

/**
 * Coupon usages info to update
 */
class UpdateInfo extends DataObject
{
    private const APPLIED_RULE_IDS_KEY = 'applied_rule_ids';
    private const COUPON_CODE_KEY = 'coupon_code';
    private const CUSTOMER_ID_KEY = 'customer_id';
    private const IS_INCREMENT_KEY = 'is_increment';

    /**
     * Get applied rule ids
     *
     * @return array
     */
    public function getAppliedRuleIds(): array
    {
        return (array)$this->getData(self::APPLIED_RULE_IDS_KEY);
    }

    /**
     * Set applied rule ids
     *
     * @param array $value
     * @return void
     */
    public function setAppliedRuleIds(array $value): void
    {
        $this->setData(self::APPLIED_RULE_IDS_KEY, $value);
    }

    /**
     * Get coupon code
     *
     * @return string
     */
    public function getCouponCode(): string
    {
        return (string)$this->getData(self::COUPON_CODE_KEY);
    }

    /**
     * Set coupon code
     *
     * @param string $value
     * @return void
     */
    public function setCouponCode(string $value): void
    {
        $this->setData(self::COUPON_CODE_KEY, $value);
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID_KEY) !== null
            ? (int) $this->getData(self::CUSTOMER_ID_KEY)
            : null;
    }

    /**
     * Set customer id
     *
     * @param int|null $value
     * @return void
     */
    public function setCustomerId(?int $value): void
    {
        $this->setData(self::CUSTOMER_ID_KEY, $value);
    }

    /**
     * Get update mode: increment - true, decrement - false
     *
     * @return bool
     */
    public function isIncrement(): bool
    {
        return (bool)$this->getData(self::IS_INCREMENT_KEY);
    }

    /**
     * Set update mode: increment - true, decrement - false
     *
     * @param bool $value
     * @return void
     */
    public function setIsIncrement(bool $value): void
    {
        $this->setData(self::IS_INCREMENT_KEY, $value);
    }
}
