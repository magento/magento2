<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Category;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Category view block on the category page.
 */
class View extends Block
{
    /**
     * Recently Viewed Products selectors.
     *
     * @var string
     */
    protected $recentlyViewedProducts =
        './/*[contains(@class,"block-viewed-products-grid")]//strong[@class="product-item-name"]';

    /**
     * New Products selectors.
     *
     * @var string
     */
    protected $newProducts = './/*[contains(@class,"block-new-products")]//strong[@class="product-item-name"]';

    /**
     * Description CSS selector.
     *
     * @var string
     */
    protected $description = '.category-description';

    /**
     * Locator for category content.
     *
     * @var string
     */
    protected $content = '.category-cms';

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_rootElement->find($this->description)->getText();
    }

    /**
     * Get Category Content.
     *
     * @return string
     */
    public function getContent()
    {
        $categoryContent = $this->_rootElement->find($this->content);
        return $categoryContent->isVisible() ? $categoryContent->getText() : '';
    }

    /**
     * Get products from Recently Viewed block.
     *
     * @return array
     */
    public function getProductsFromRecentlyViewedBlock()
    {
        $products = [];
        $this->waitForElementVisible($this->recentlyViewedProducts, Locator::SELECTOR_XPATH);
        $productNames = $this->_rootElement->getElements($this->recentlyViewedProducts, Locator::SELECTOR_XPATH);
        foreach ($productNames as $productName) {
            $products[] = $productName->getText();
        }
        return $products;
    }

    /**
     * Get products from Catalog New Products List block.
     *
     * @return array
     */
    public function getProductsFromCatalogNewProductsListBlock()
    {
        $products = [];
        $this->waitForElementVisible($this->newProducts, Locator::SELECTOR_XPATH);
        $productNames = $this->_rootElement->getElements($this->newProducts, Locator::SELECTOR_XPATH);
        foreach ($productNames as $productName) {
            $products[] = $productName->getText();
        }
        return $products;
    }
}
