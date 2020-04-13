<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Model\Category\Toolbar\Config as ToolbarConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit  = [10 => 10,20 => 20,50 => 50];

    /**
     * @var ToolbarConfig
     */
    private $toolbarConfig;

    /**
     * ProductList constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ToolbarConfig $toolbarConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ToolbarConfig $toolbarConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->toolbarConfig = $toolbarConfig;
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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
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
        return $this->toolbarConfig->getOrderField();
    }

    /**
     * Retrieve available limits for specified view mode
     *
     * @param string $mode
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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if ($this->scopeConfig->isSetFlag(
            'catalog/frontend/list_allow_all',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return ($perPageValues + ['all' => __('All')]);
        } else {
            return $perPageValues;
        }
    }

    /**
     * Retrieve default per page values
     *
     * @param string $viewMode
     * @return string (comma separated)
     */
    public function getDefaultLimitPerPageValue($viewMode)
    {
        if ($viewMode == self::VIEW_MODE_LIST) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/list_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } elseif ($viewMode == self::VIEW_MODE_GRID) {
            return $this->scopeConfig->getValue(
                'catalog/frontend/grid_per_page',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return 0;
    }
}
