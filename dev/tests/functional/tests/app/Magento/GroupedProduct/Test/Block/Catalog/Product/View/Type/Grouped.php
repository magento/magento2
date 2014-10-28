<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GroupedProduct\Test\Block\Catalog\Product\View\Type;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Grouped
 * Grouped product blocks on frontend
 */
class Grouped extends Block
{
    /**
     * Selector qty for sub product by id
     *
     * @var string
     */
    protected $qtySubProductById = '[name="super_group[%d]"]';

    /**
     * Selector for sub product block by name
     *
     * @var string
     */
    protected $subProductByName = './/tr[./td[contains(@class,"item")] and .//*[contains(.,"%s")]]';

    /**
     * Selector for sub product name
     *
     * @var string
     */
    protected $productName = '.product.name';

    /**
     * Selector for sub product price
     *
     * @var string
     */
    protected $price = '.price.price';

    /**
     * Selector for qty of sub product
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
     * Fill product options on view page
     *
     * @param FixtureInterface $product
     * @return void
     */
    public function fill(FixtureInterface $product)
    {
        /** @var GroupedProductInjectable $product */
        $associatedProducts = $product->getAssociated()['products'];
        $data = $product->getCheckoutData()['options'];

        // Replace link key to label
        foreach ($data as $key => $productData) {
            $productKey = str_replace('product_key_', '', $productData['name']);
            $data[$key]['name'] = $associatedProducts[$productKey]->getName();
        }

        // Fill
        foreach ($data as $productData) {
            $subProduct = $this->_rootElement->find(
                sprintf($this->subProductByName, $productData['name']),
                Locator::SELECTOR_XPATH
            );
            $subProduct->find($this->qty)->setValue($productData['qty']);
        }
    }

    /**
     * Return product options on view page
     *
     * @param FixtureInterface $product
     * @return array
     */
    public function getOptions(FixtureInterface $product)
    {

        $options = [];
        if ($product instanceof InjectableFixture) {
            /** @var GroupedProductInjectable $product */
            $associatedProducts = $product->getAssociated()['products'];
        } else {
            // TODO: Removed after refactoring(removed) old product fixture.
            /** @var GroupedProduct $product */
            $associatedProducts = $product->getAssociatedProducts();
        }

        foreach ($associatedProducts as $subProduct) {
            /** @var CatalogProductSimple $subProduct */
            $subProductBlock = $this->_rootElement->find(
                sprintf($this->subProductByName, $subProduct->getName()),
                Locator::SELECTOR_XPATH
            );

            $options[] = [
                'name' => $subProductBlock->find($this->productName)->getText(),
                'price' => $subProductBlock->find($this->price)->getText(),
                'qty' => $subProductBlock->find($this->qty)->getValue()
            ];
        }

        return $options;
    }
}
