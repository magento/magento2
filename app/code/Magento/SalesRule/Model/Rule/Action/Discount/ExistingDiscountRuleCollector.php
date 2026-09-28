<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Rule\Action\Discount;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

class ExistingDiscountRuleCollector implements ResetAfterRequestInterface
{
    /**
     * @var array
     */
    private array $ruleDiscounts = [];

    /**
     * Store discounts that are applied to affected items by previous rules
     *
     * @param int $ruleId
     * @param float $discountAmount
     * @return void
     */
    public function setExistingRuleDiscount(int $ruleId, float $discountAmount): void
    {
        $this->ruleDiscounts[$ruleId] = $discountAmount;
    }

    /**
     * Retrieve discount that was applied to affected items by previous rule
     *
     * @param int $ruleId
     * @return float|null
     */
    public function getExistingRuleDiscount(int $ruleId): ?float
    {
        return $this->ruleDiscounts[$ruleId] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->ruleDiscounts = [];
    }
}
