<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Catalog\Product;

use Magento\Catalog\Test\Block\Product\View as ParentView;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class View
 * Grouped product view block on the product page
 */
class View extends ParentView
{
    /**
     * Block grouped product
     *
     * @var string
     */
    protected $groupedProductBlock = '[class="table-wrapper grouped"]';

    /**
     * This member holds the class name of the tier price block.
     *
     * @var string
     */
    protected $formatTierPrice =
        "//tr[@class='row-tier-price'][%row-number%]//ul[contains(@class,'tier')]//*[@class='item'][%line-number%]";

    /**
     * This member holds the class name of the special price block.
     *
     * @var string
     */
    protected $formatSpecialPrice = '//tbody//tr[%row-number%]//*[contains(@class,"price-box")]';

    /**
     * Get grouped product block
     *
     * @return \Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type\Grouped
     */
    public function getGroupedProductBlock()
    {
        return $this->blockFactory->create(
            \Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type\Grouped::class,
            [
                'element' => $this->_rootElement->find($this->groupedProductBlock)
            ]
        );
    }

    /**
     * Change tier price selector
     *
     * @param int $index
     * @return void
     */
    public function itemTierPriceProductBlock($index)
    {
        $this->tierPricesSelector = str_replace('%row-number%', $index, $this->formatTierPrice);
    }

    /**
     * Change tier price selector
     *
     * @param int $index
     * @return void
     */
    public function itemPriceProductBlock($index)
    {
        $this->priceBlock = str_replace('%row-number%', $index, $this->formatSpecialPrice);
    }

    /**
     * Return product options
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {
        return ['grouped_options' => $this->getGroupedProductBlock()->getOptions($product)];
    }

    /**
     * Fill specified option for the product
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fillOptions(FixtureInterface $product)
    {
        $this->getGroupedProductBlock()->fill($product);
    }

    /**
     * Set quantity and click add to cart.
     * @param FixtureInterface $product
     * @param string|int $qty
     */
    public function setQtyAndClickAddToCartGrouped(FixtureInterface $product, $qty)
    {
        $associatedProducts = $product->getAssociated()['products'];
        $groupedProductBlock = $this->getGroupedProductBlock();
        foreach ($associatedProducts as $product) {
            $groupedProductBlock->setQty($product->getId(), $qty);
        }
        $this->clickAddToCart();
    }
}
