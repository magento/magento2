<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Product\ProductList;

use Mtf\Block\Block;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;

/**
 * Class Related
 * Related product block on the page
 */
class Related extends Block
{
    /**
     * Related product locator on the page
     *
     * @var string
     */
    protected $relatedProduct = "//div[normalize-space(div//a)='%s']";

    /**
     * Checking related product visibility
     *
     * @param string $productName
     * @return bool
     */
    public function isRelatedProductVisible($productName)
    {
        return $this->getProductElement($productName)->isVisible();
    }

    /**
     * Verify that you can choose the related products
     *
     * @param string $productName
     * @return bool
     */
    public function isRelatedProductSelectable($productName)
    {
        return $this->getProductElement($productName)->find("[name='related_products[]']")->isVisible();
    }

    /**
     * Open related product
     *
     * @param string $productName
     * @return void
     */
    public function openRelatedProduct($productName)
    {
        $this->getProductElement($productName)->find('.product.name>a')->click();
    }

    /**
     * Select related product
     *
     * @param string $productName
     * @return void
     */
    public function selectProductForAddToCart($productName)
    {
        $this->getProductElement($productName)
            ->find("[name='related_products[]']", Locator::SELECTOR_CSS, 'checkbox')
            ->setValue('Yes');
    }

    /**
     * Get related product element
     *
     * @param string $productName
     * @return Element
     */
    private function getProductElement($productName)
    {
        return $this->_rootElement->find(sprintf($this->relatedProduct, $productName), Locator::SELECTOR_XPATH);
    }
}
