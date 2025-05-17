<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class CatalogRule
{
    private const XML_PATH_SHARE_ALL_CATALOG_RULES = 'catalog/rule/share_all_catalog_rules';
    private const XML_PATH_SHARE_APPLIED_CATALOG_RULES = 'catalog/rule/share_applied_catalog_rules';

    /**
     * CatalogRule constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Is 'share_all_catalog_rules' config enabled
     *
     * @return bool
     */
    public function isShareAllCatalogRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHARE_ALL_CATALOG_RULES);
    }

    /**
     * Is 'share_applied_catalog_rules' config enabled
     *
     * @return bool
     */
    public function isShareAppliedCatalogRulesEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SHARE_APPLIED_CATALOG_RULES);
    }
}
