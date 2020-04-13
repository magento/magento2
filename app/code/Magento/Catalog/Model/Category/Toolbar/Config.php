<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category\Toolbar;

use Magento\Catalog\Model\Category\Config as CategoryConfig;
use Magento\Catalog\Model\CurrentCategory;
use Magento\Framework\Exception\NoSuchEntityException;

class Config
{
    /**
     * @var CurrentCategory
     */
    private $currentCategory;

    /**
     * @var CategoryConfig
     */
    private $categoryConfig;

    /**
     * Config constructor.
     * @param CurrentCategory $currentCategory
     * @param CategoryConfig $categoryConfig
     */
    public function __construct(
        CurrentCategory $currentCategory,
        CategoryConfig $categoryConfig
    ) {
        $this->currentCategory = $currentCategory;
        $this->categoryConfig = $categoryConfig;
    }

    /**
     * Returns an order field from a category default_sort_by attribute,
     * or if it is not set, the default sort field value from the configuration.
     *
     * @return string
     */
    public function getOrderField(): string
    {
        try {
            $category = $this->currentCategory->get();
        } catch (NoSuchEntityException $exception) {
            return $this->categoryConfig->getDefaultSortField();
        }

        if ($category->getDefaultSortBy()) {
            return $category->getDefaultSortBy();
        }

        return $this->categoryConfig->getDefaultSortField();
    }
}
