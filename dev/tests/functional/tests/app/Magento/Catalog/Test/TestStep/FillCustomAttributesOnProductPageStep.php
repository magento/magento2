<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Fill custom attributes values for product on product page step.
 */
class FillCustomAttributesOnProductPageStep implements TestStepInterface
{
    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Catalog Product Index page.
     *
     * @var CatalogProductIndex
     */
    private $productGrid;

    /**
     * Catalog Product Edit page.
     *
     * @var CatalogProductEdit
     */
    private $editProductPage;

    /**
     * Attributes value to fill on product page.
     *
     * @var array;
     */
    private $attributesValue;

    /**
     * Attributes that must be filled on product page.
     *
     * @var CatalogProductAttribute[];
     */
    private $attributes;

    /**
     * Products that shoud be updated.
     *
     * @var FixtureInterface[]
     */
    private $products;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $editProductPage
     * @param array $productAttributesValue
     * @param array $attribute
     * @param array $products
     * @return void
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $editProductPage,
        array $productAttributesValue,
        array $attribute,
        array $products
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->productGrid = $productGrid;
        $this->editProductPage = $editProductPage;
        $this->attributesValue = $productAttributesValue;
        $this->attributes = $attribute;
        $this->products = $products;
    }

    /**
     * Fills custom attributes values for product on product page.
     *
     * @return void
     */
    public function run()
    {
        $this->productGrid->open();

        foreach ($this->products as $product) {
            $filter = ['sku' => $product->getSku()];
            $this->productGrid->getProductGrid()->searchAndOpen($filter);

            foreach ($this->attributes as $key => $attribute) {
                $customAttribute = ['value' => $this->attributesValue[$key], 'attribute' => $attribute];
                /** @var CatalogProductSimple $updatedProduct */
                $updatedProduct = $this->fixtureFactory->createByCode(
                    'catalogProductSimple',
                    [
                        'data' => [
                            'custom_attribute' => $customAttribute
                        ],
                    ]
                );
                $this->editProductPage->getProductForm()->fill($updatedProduct);
            }
            $this->editProductPage->getFormPageActions()->save();
        }
    }
}
