<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Model\Resolver;

use Magento\CatalogRule\Model\Config\CatalogRule;
use Magento\CatalogRule\Model\ResourceModel\GetAppliedCatalogRules;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides data for product.rules.name
 */
class AppliedCatalogRules implements ResolverInterface
{
    /**
     * AppliedCatalogRules Constructor
     *
     * @param CatalogRule $config
     * @param GetAppliedCatalogRules $getAppliedCatalogRules
     */
    public function __construct(
        private readonly CatalogRule $config,
        private readonly GetAppliedCatalogRules $getAppliedCatalogRules
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        if (!$this->config->isShareAppliedCatalogRulesEnabled()) {
            return null; //Returning `null` to ensure that the entire product response remains intact.
        }

        return array_map(
            fn ($rule) => ['name' => $rule['name']],
            $this->getAppliedCatalogRules->execute(
                (int)$value['model']->getId(),
                (int)$context->getExtensionAttributes()->getStore()->getWebsiteId()
            )
        );
    }
}
