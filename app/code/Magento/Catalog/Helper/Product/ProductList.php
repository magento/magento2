<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Returns data for toolbars of Sorting and Pagination
 *
 * @api
 * @since 100.0.2
 */
class ProductList
{
    public const XML_PATH_LIST_MODE = 'catalog/frontend/list_mode';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    const VIEW_MODE_LIST = 'list';
    const VIEW_MODE_GRID = 'grid';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit = [10 => 10, 20 => 20, 50 => 50];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $coreRegistry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Registry $coreRegistry = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry ?? ObjectManager::getInstance()->get(Registry::class);
    }

    /**
     * Returns available mode for view
     *
     * @return array|null
     */
    public function getAvailableViewMode()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_LIST_MODE, ScopeInterface::SCOPE_STORE);

        switch ($value) {
            case 'grid':
                return ['grid' => __('Grid')];

            case 'list':
                return ['list' => __('List')];

            case 'grid-list':
                return ['grid' => __('Grid'), 'list' => __('List')];

            case 'list-grid':
                return ['list' => __('List'), 'grid' => __('Grid')];
        }

        return null;
    }

    /**
     * Returns default view mode
     *
     * @param array $options
     * @return string
     */
    public function getDefaultViewMode($options = [])
    {
        if (empty($options)) {
            $options = $this->getAvailableViewMode();
        }

        return current(array_keys($options));
    }

    /**
     * Get default sort field
     *
     * @FIXME Helper should be context-independent
     * @return null|string
     */
    public function getDefaultSortField()
    {
        $currentCategory = $this->coreRegistry->registry('current_category');
        if ($currentCategory) {
            return $currentCategory->getDefaultSortBy();
        }

        return $this->scopeConfig->getValue(Config::XML_PATH_LIST_DEFAULT_SORT_BY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $viewMode
     * @return array
     */
    public function getAvailableLimit($viewMode): array
    {
        $availableViewModes = $this->getAvailableViewMode();

        if (!isset($availableViewModes[$viewMode])) {
            return $this->_defaultAvailableLimit;
        }

        $perPageConfigPath = 'catalog/frontend/' . $viewMode . '_per_page_values';
        $perPageValues = (string)$this->scopeConfig->getValue($perPageConfigPath, ScopeInterface::SCOPE_STORE);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag('catalog/frontend/list_allow_all', ScopeInterface::SCOPE_STORE)) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }

    /**
     * Returns default value of `per_page` for view mode provided
     *
     * @param string $viewMode
     * @return int
     */
    public function getDefaultLimitPerPageValue($viewMode): int
    {
        $xmlConfigPath = sprintf('catalog/frontend/%s_per_page', $viewMode);
        $defaultLimit = $this->scopeConfig->getValue($xmlConfigPath, ScopeInterface::SCOPE_STORE);

        $availableLimits = $this->getAvailableLimit($viewMode);
        return (int)($availableLimits[$defaultLimit] ?? current($availableLimits));
    }
}
