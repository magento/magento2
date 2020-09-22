<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\ViewModel;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * View model for search
 */
class ConfigProvider implements ArgumentInterface
{
    /**
     * Suggestions settings config paths
     */
    private const SEARCH_SUGGESTION_ENABLED = 'catalog/search/search_suggestion_enabled';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is Search Suggestions Allowed
     *
     * @return bool
     */
    public function isSuggestionsAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::SEARCH_SUGGESTION_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
}
