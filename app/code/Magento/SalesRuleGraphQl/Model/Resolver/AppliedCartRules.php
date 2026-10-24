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
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\SalesRule\Model\Config;

/**
 * Resolver class for providing All applied cart rules
 */
class AppliedCartRules implements ResolverInterface
{
    /**
     * AppliedCartRules Constructor
     *
     * @param Config $config
     * @param Uid $idEncoder
     */
    public function __construct(
        private readonly Config $config,
        private readonly Uid $idEncoder
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
    ): ?array {
        if (empty($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!$this->config->isShareAppliedCartRulesEnabled()) {
            return null;
        }

        $ruleIds = $value['model']->getAppliedRuleIds();

        return $ruleIds ? array_map(
            fn ($rule) => ['uid' => $this->idEncoder->encode($rule)],
            explode(",", $ruleIds)
        ) : [];
    }
}
