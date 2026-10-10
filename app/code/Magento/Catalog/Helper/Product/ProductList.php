<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
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

    public const VIEW_MODE_LIST = 'list';
    public const VIEW_MODE_GRID = 'grid';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Registry
     * @deprecated 100.0.0 Registry is no longer used. Category is resolved via CategoryRepositoryInterface.
     * @see \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $coreRegistry;

    /** @var CategoryRepositoryInterface */
    private CategoryRepositoryInterface $categoryRepository;

    /** @var RequestInterface */
    private RequestInterface $request;

    /**
     * Default limits per page
     *
     * @var array
     */
    protected $_defaultAvailableLimit = [10 => 10, 20 => 20, 50 => 50];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry|null $coreRegistry @deprecated - no longer used
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @param RequestInterface|null $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ?Registry $coreRegistry = null,
        ?CategoryRepositoryInterface $categoryRepository = null,
        ?RequestInterface $request = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository
            ?? ObjectManager::getInstance()->get(CategoryRepositoryInterface::class);
        $this->request = $request
            ?? ObjectManager::getInstance()->get(RequestInterface::class);
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
     * @return null|string
     */
    public function getDefaultSortField(): ?string
    {
        $categoryId = (int) $this->request->getParam('id');
        if ($categoryId) {
            try {
                $category = $this->categoryRepository->get($categoryId);
                $sortBy = $category->getDefaultSortBy();
                if ($sortBy) {
                    return $sortBy;
                }
            } catch (NoSuchEntityException $e) {
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            }
        }

        return $this->scopeConfig->getValue(
            Config::XML_PATH_LIST_DEFAULT_SORT_BY,
            ScopeInterface::SCOPE_STORE
        );
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
        
        if ($defaultLimit !== null && isset($availableLimits[$defaultLimit])) {
            return (int)$availableLimits[$defaultLimit];
        }
        
        return (int)current($availableLimits);
    }
}
