<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRuleGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Model\Config\Coupon;
use Magento\SalesRule\Model\ResourceModel\GetAppliedCartRules;

class AppliedCartRules implements ResolverInterface
{
    /**
     * AppliedCartRules Constructor
     *
     * @param Coupon $config
     * @param GetAppliedCartRules $getAppliedCartRules
     */
    public function __construct(
        private readonly Coupon $config,
        private readonly GetAppliedCartRules $getAppliedCartRules
    ) {
    }

    /**
     * @inheritDoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!(($value['model'] ?? null) instanceof CartInterface)) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!$this->config->isShareAppliedSalesRulesEnabled()) {
            return null; //returning null so that whole cart response is not broken
        }

        $ruleIds = $value['model']->getAppliedRuleIds();

        return $ruleIds ? array_map(
            fn ($rule) => ['name' => $rule['name']],
            $this->getAppliedCartRules->execute($ruleIds, $context)
        ) : [];
    }
}
