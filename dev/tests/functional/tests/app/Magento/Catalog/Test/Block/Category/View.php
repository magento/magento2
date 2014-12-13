<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Category;

use Magento\Widget\Test\Fixture\Widget;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class View
 * Category view block on the category page
 */
class View extends Block
{
    /**
     * Recently Viewed Products selectors
     *
     * @var string
     */
    protected $recentlyViewedProducts = './/*[contains(@class,"widget")]//strong[@class="product-item-name"]';

    /**
     * Description CSS selector
     *
     * @var string
     */
    protected $description = '.category-description';

    /**
     * Locator for category content
     *
     * @var string
     */
    protected $content = '.category-cms';

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_rootElement->find($this->description)->getText();
    }

    /**
     * Get Category Content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_rootElement->find($this->content)->getText();
    }

    /**
     * Get products from Recently Viewed block
     *
     * @return array
     */
    public function getProductsFromRecentlyViewedBlock()
    {
        $products = [];
        $this->waitForElementVisible($this->recentlyViewedProducts, Locator::SELECTOR_XPATH);
        $productNames = $this->_rootElement->find($this->recentlyViewedProducts, Locator::SELECTOR_XPATH)
            ->getElements();
        foreach ($productNames as $productName) {
            $products[] = $productName->getText();
        }
        return $products;
    }
}
