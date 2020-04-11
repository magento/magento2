<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Config as CatalogConfig;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string|int|null $scopeCode
     * @return string
     */
    public function getDefaultSortField($scopeCode = null): string
    {
        return (string) $this->scopeConfig->getValue(
            CatalogConfig::XML_PATH_LIST_DEFAULT_SORT_BY,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
}
