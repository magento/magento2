<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRuleGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesRule\Model\Config\Coupon;
use Magento\SalesRule\Model\ResourceModel\GetAllCartRules;

class AllCartRules implements ResolverInterface
{
    /**
     * AllCartRules Constructor
     *
     * @param Coupon $config
     * @param GetAllCartRules $getAllCartRules
     */
    public function __construct(
        private readonly Coupon $config,
        private readonly GetAllCartRules $getAllCartRules
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
        if (!$this->config->isShareAllSalesRulesEnabled()) {
            throw new GraphQlInputException(__('Sharing Cart Rules information is disabled or not configured.'));
        }

        return array_map(
            static fn ($rule) => ['name' => $rule['name']],
            $this->getAllCartRules->execute($context)
        );
    }
}
