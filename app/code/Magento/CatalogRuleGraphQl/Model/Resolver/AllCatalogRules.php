<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRuleGraphQl\Model\Resolver;

use Magento\CatalogRule\Model\Config\CatalogRule;
use Magento\CatalogRule\Model\ResourceModel\GetAllCatalogRules;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Provides data for allCatalogRules.name
 */
class AllCatalogRules implements ResolverInterface
{
    /**
     * AllCatalogRules Constructor
     *
     * @param CatalogRule $config
     * @param GetAllCatalogRules $getAllCatalogRules
     */
    public function __construct(
        private readonly CatalogRule $config,
        private readonly GetAllCatalogRules $getAllCatalogRules
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
        if (!$this->config->isShareAllCatalogRulesEnabled()) {
            throw new GraphQlInputException(__('Sharing catalog rules information is disabled or not configured.'));
        }

        return array_map(
            fn ($rule) => ['name' => $rule['name']],
            $this->getAllCatalogRules->execute(
                (int)$context->getExtensionAttributes()->getStore()->getWebsiteId()
            )
        );
    }
}
