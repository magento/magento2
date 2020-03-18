<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ProductList
 *
 * @api
 * @since 100.0.2
 */
class ProductList
{
    /**
     * List mode configuration path
     */
    const XML_PATH_LIST_MODE = 'catalog/frontend/list_mode';

    const VIEW_MODE_LIST = 'list';
    const VIEW_MODE_GRID = 'grid';

    const DEFAULT_SORT_DIRECTION = 'asc';
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
    protected $_defaultAvailableLimit  = [10 => 10,20 => 20,50 => 50];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry|null $coreRegistry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ?Registry $coreRegistry = null
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
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_LIST_MODE,
            ScopeInterface::SCOPE_STORE
        );

        switch ($value) {
            case 'grid':
                $availableMode = ['grid' => __('Grid')];
                break;

            case 'list':
                $availableMode = ['list' => __('List')];
                break;

            case 'grid-list':
                $availableMode = ['grid' => __('Grid'), 'list' =>  __('List')];
                break;

            case 'list-grid':
                $availableMode = ['list' => __('List'), 'grid' => __('Grid')];
                break;
            default:
                $availableMode = null;
                break;
        }
        return $availableMode;
    }

    /**
     * Returns default view mode
     *
     * @param array $options
     *
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
     * @return null|string
     */
    public function getDefaultSortField()
    {
        $currentCategory = $this->coreRegistry->registry('current_category');
        if ($currentCategory) {
            return $currentCategory->getDefaultSortBy();
        }

        return $this->scopeConfig->getValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $mode
     *
     * @return array
     */
    public function getAvailableLimit($mode)
    {
        if (!in_array($mode, [self::VIEW_MODE_GRID, self::VIEW_MODE_LIST])) {
            return $this->_defaultAvailableLimit;
        }
        $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
        $perPageValues = (string)$this->scopeConfig->getValue(
            $perPageConfigKey,
            ScopeInterface::SCOPE_STORE
        );
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag(
            'catalog/frontend/list_allow_all',
            ScopeInterface::SCOPE_STORE
        )) {
            return ($perPageValues + ['all' => __('All')]);
        }

        return $perPageValues;
    }

    /**
     * Retrieve default per page values
     *
     * @param string $viewMode
     *
     * @return string (comma separated)
     */
    public function getDefaultLimitPerPageValue($viewMode)
    {
        if ($viewMode == self::VIEW_MODE_LIST) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/list_per_page',
                ScopeInterface::SCOPE_STORE
            );
        }

        if ($viewMode == self::VIEW_MODE_GRID) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/grid_per_page',
                ScopeInterface::SCOPE_STORE
            );
        }

        return 0;
    }
}
