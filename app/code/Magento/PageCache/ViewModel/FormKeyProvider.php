<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\ViewModel;

use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\PageCache\Model\Config;

/**
 * Adds script to update form key from cookie after script rendering
 */
class FormKeyProvider implements ArgumentInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ScopeConfigInterface object
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is full page cache enabled
     *
     * @return bool
     */
    public function isFullPageCacheEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Is category pagination cache enabled
     *
     * @return bool
     */
    public function isPaginationCacheEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(ToolbarMemorizer::XML_PATH_CATALOG_REMEMBER_PAGINATION);
    }
}
