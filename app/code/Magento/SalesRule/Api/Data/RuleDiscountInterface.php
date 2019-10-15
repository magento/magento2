<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Api\Data;

/**
 * @api
 */
interface RuleDiscountInterface
{
    /**
     * Get Discount Data
     *
     * @return \Magento\SalesRule\Api\Data\DiscountDataInterface
     */
    public function getDiscountData(): DiscountDataInterface;

    /**
     * Set discount data
     *
     * @param DiscountDataInterface $discountData
     * @return $this
     */
    public function setDiscountData(DiscountDataInterface $discountData);

    /**
     * Set Rule Label
     *
     * @param string $ruleLabel
     * @return $this
     */
    public function setRuleLabel(string $ruleLabel);

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel(): ?string;

    /**
     * Set Rule Id
     *
     * @param int $ruleID
     * @return $this
     */
    public function setRuleID(int $ruleID);

    /**
     * Get Rule ID
     *
     * @return int
     */
    public function getRuleID(): ?int;
}
