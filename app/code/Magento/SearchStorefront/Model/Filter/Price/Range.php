<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SearchStorefront\Model\Filter\Price;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Range
{
    const XML_PATH_RANGE_STEP = 'catalog/layered_navigation/price_range_step';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPriceRange()
    {
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();
        $category = $this->categoryRepository->get($rootCategoryId);
        return $category->getFilterPriceRange() ?? $this->getConfigRangeStep($this->storeManager->getStore()->getId());
    }

    /**
     * @param $storeId
     * @return float
     */
    public function getConfigRangeStep($storeId)
    {
        return (double) $this->scopeConfig->getValue(
            self::XML_PATH_RANGE_STEP,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
