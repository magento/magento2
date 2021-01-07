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
use Magento\Search\Helper\Data as SearchHelper;

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
     * @var SearchHelper
     */
    private $searchHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SearchHelper $searchHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SearchHelper $searchHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->searchHelper = $searchHelper;
    }

    /**
     * Retrieve search helper instance for template view
     *
     * @return SearchHelper
     */
    public function getSearchHelperData(): SearchHelper
    {
        return $this->searchHelper;
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
