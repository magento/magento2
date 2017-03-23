<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Class Grouped
 * Grouped product blocks on frontend.
 */
class Grouped extends Block
{
    /**
     * Selector qty for sub product by id.
     *
     * @var string
     */
    protected $qtySubProductById = '[name="super_group[%d]"]';

    /**
     * Selector for sub product block by name.
     *
     * @var string
     */
    protected $subProductByName = './/tr[./td[contains(@class,"item")] and .//*[contains(.,"%s")]]';

    /**
     * Selector for sub product name.
     *
     * @var string
     */
    protected $productName = '.product-item-name';

    /**
     * Selector for sub product price.
     *
     * @var string
     */
    protected $price = '.price.price';

    /**
     * Selector for qty of sub product.
     *
     * @var string
     */
    protected $qty = '[name^="super_group"]';

    /**
     * Get qty for subProduct
     *
     * @param int $subProductId
     * @return string
     */
    public function getQty($subProductId)
    {
        return $this->_rootElement->find(sprintf($this->qtySubProductById, $subProductId))->getValue();
    }

    /**
     * Set qty to subproduct block
     *
     * @param int $subProductId
     * @param string|int $qty
     * @return void
     */
    public function setQty($subProductId, $qty)
    {
        $this->_rootElement->find(sprintf($this->qtySubProductById, $subProductId))->setValue($qty);
    }

    /**
     * Fill product options on view page.
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fill(FixtureInterface $product)
    {
        /** @var GroupedProduct $product */
        $associatedProducts = $product->getAssociated()['products'];
        $checkoutData = $product->getCheckoutData();
        if (isset($checkoutData['options'])) {
            // Replace link key to label
            foreach ($checkoutData['options'] as $key => $productData) {
                $productKey = str_replace('product_key_', '', $productData['name']);
                $checkoutData['options'][$key]['name'] = $associatedProducts[$productKey]->getName();
            }

            // Fill
            foreach ($checkoutData['options'] as $productData) {
                $this->browser->selectWindow();
                $subProduct = $this->_rootElement->find(
                    sprintf($this->subProductByName, $productData['name']),
                    Locator::SELECTOR_XPATH
                );
                $subProduct->find($this->qty)->setValue($productData['qty']);
                $this->_rootElement->click();
            }
        }
    }

    /**
     * Return product options on view page.
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {
        /** @var GroupedProduct $product */
        $associatedProducts = $product->getAssociated()['products'];
        $options = [];

        foreach ($associatedProducts as $subProduct) {
            /** @var CatalogProductSimple $subProduct */
            $subProductBlock = $this->_rootElement->find(
                sprintf($this->subProductByName, $subProduct->getName()),
                Locator::SELECTOR_XPATH
            );

            $options[] = [
                'name' => $subProductBlock->find($this->productName)->getText(),
                'price' => $subProductBlock->find($this->price)->getText(),
                'qty' => $subProductBlock->find($this->qty)->getValue(),
            ];
        }

        return $options;
    }
}
