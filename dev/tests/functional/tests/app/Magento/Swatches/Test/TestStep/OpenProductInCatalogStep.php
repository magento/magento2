<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\TestStep;

use Magento\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Update configurable product step.
 */
class OpenProductInCatalogStep implements TestStepInterface
{
    /**
     * @var CatalogCategoryView
     */
    private $catalogCategoryView;

    /**
     * @var BrowserInterface
     */
    private $browser;

    /**
     * @var \Magento\ConfigurableProduct\Test\Repository\ConfigurableProduct
     */
    private $product;

    /**
     * OpenProductInCatalog constructor.
     *
     * @param CatalogCategoryView $categoryPage
     * @param BrowserInterface $browser
     * @param CmsIndex $cmsIndex
     * @param Category $category
     * @param \Magento\Swatches\Test\Fixture\ConfigurableProduct $product
     */
    public function __construct(
        CatalogCategoryView $categoryPage,
        BrowserInterface $browser,
        CmsIndex $cmsIndex,
        Category $category,
        \Magento\Swatches\Test\Fixture\ConfigurableProduct $product
    ) {
        $this->catalogCategoryView = $categoryPage;
        $this->browser = $browser;
        $this->product = $product;
        $this->cmsIndex = $cmsIndex;
        $this->category = $category;
    }

    /**
     * Update configurable product.
     *
     * @return array
     */
    public function run()
    {
        $categoryName = $this->product->hasData('category_ids') ? $this->product->getCategoryIds()[0] : $this->category->getName();
        $this->cmsIndex->open();
        $this->cmsIndex->getTopmenu()->selectCategoryByName($categoryName);
    }
}
