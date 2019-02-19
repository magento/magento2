<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Update child of configurable product step.
 */
class UpdateChildProductStep implements TestStepInterface
{
    /**
     * Attribute key.
     *
     * @var string
     */
    private $attributeKey = 'attribute_key_0';

    /**
     * Product fixture.
     *
     * @var ConfigurableProduct
     */
    private $product;

    /**
     * Product grid.
     *
     * @var CatalogProductIndex
     */
    private $productGrid;

    /**
     * Product edit page.
     *
     * @var CatalogProductEdit
     */
    private $productEdit;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Product update data.
     *
     * @var array
     */
    private $productUpdate;

    /**
     * @param ConfigurableProduct $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productEdit
     * @param FixtureFactory $fixtureFactory
     * @param array $productUpdate
     */
    public function __construct(
        ConfigurableProduct $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productEdit,
        FixtureFactory $fixtureFactory,
        array $productUpdate
    ) {
        $this->product = $product;
        $this->productGrid = $productGrid;
        $this->productEdit = $productEdit;
        $this->fixtureFactory = $fixtureFactory;
        $this->productUpdate = $productUpdate;
    }

    /**
     * Update child of configurable product.
     *
     * @return array
     */
    public function run()
    {
        $items = explode(',', $this->productUpdate['optionNumber']);

        foreach ($items as $itemIndex) {
            $index = (int)$itemIndex - 1;
            $optionKey = 'option_key_' . $index;
            $matrixIndex = $this->attributeKey . ':' . $optionKey;
            $sku = $this->product->getConfigurableAttributesData()['matrix'][$matrixIndex]['sku'];
            $this->fillChildProductData($sku);
            $this->prepareResultProduct($matrixIndex, $optionKey);
        }

        return ['product' => $this->product];
    }

    /**
     * Prepare configurable product fixture.
     *
     * @param string $matrixIndex
     * @param string $optionKey
     * @return void
     */
    private function prepareResultProduct($matrixIndex, $optionKey)
    {
        $product = $this->product->getData();
        $attributeKey = 'configurable_attributes_data';

        if (isset($this->productUpdate['newPrice'])) {
            $product[$attributeKey]['matrix'][$matrixIndex]['price'] = $this->productUpdate['newPrice'];
            $product[$attributeKey]['attributes_data'][$this->attributeKey]['options'][$optionKey]['pricing_value']
                = $this->productUpdate['newPrice'];
        } else {
            unset($product[$attributeKey]['matrix'][$matrixIndex]);
            unset($product[$attributeKey]['attributes_data'][$this->attributeKey]['options'][$optionKey]);
        }

        $product['category_ids']['category']
            = $this->product->getDataFieldConfig('category_ids')['source']->getCategories()[0];
        $product['price'] = $this->getLowestConfigurablePrice($product);

        if (!empty($product['configurable_attributes_data']['attributes_data'][$this->attributeKey]['options'])) {
            $this->product = $this->fixtureFactory->createByCode('configurableProduct', ['data' => $product]);
        }
    }

    /**
     * Fill data of child product.
     *
     * @param string $sku
     * @return void
     */
    private function fillChildProductData($sku)
    {
        $this->productGrid->open();
        $this->productGrid->getProductGrid()->searchAndOpen(['sku' => $sku]);

        if (isset($this->productUpdate['switchScope']) && $this->productUpdate['switchScope']) {
            $store = $this->fixtureFactory->createByCode('store', ['dataset' => 'default']);
            $this->productEdit->getFormPageActions()->changeStoreViewScope($store);
        }

        if (isset($this->productUpdate['childProductUpdate']['unassignFromWebsite'])) {
            $this->productEdit->getProductForm()->unassignFromWebsite(
                $this->productUpdate['childProductUpdate']['unassignFromWebsite']
            );
        } else {
            $fixture = $this->fixtureFactory->createByCode(
                'catalogProductSimple',
                $this->productUpdate['childProductUpdate']
            );
            $this->productEdit->getProductForm()->fill($fixture);
        }

        $this->productEdit->getFormPageActions()->save();
    }

    /**
     * Returns lowest possible price of configurable product.
     *
     * @param array $product
     * @return string
     */
    private function getLowestConfigurablePrice(array $product)
    {
        $configurableOptions = $product['configurable_attributes_data'];
        $attributeOption = reset($configurableOptions['matrix']);
        $price = isset($attributeOption['price']) ? $attributeOption['price'] : "0";

        foreach ($configurableOptions['matrix'] as $option) {
            if ($price > $option['price']) {
                $price = $option['price'];
            }
        }

        return $price;
    }
}
