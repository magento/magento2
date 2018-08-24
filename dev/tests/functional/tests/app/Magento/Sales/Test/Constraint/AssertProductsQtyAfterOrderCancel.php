<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Catalog\Test\Constraint\AssertProductForm;
use Magento\ConfigurableProduct\Test\Constraint\AssertConfigurableProductForm;

/**
 * Assert that products quantity was increased after order cancel.
 */
class AssertProductsQtyAfterOrderCancel extends AbstractConstraint
{
    /**
     * Skip fields for create product fixture.
     *
     * @var array
     */
    protected $skipFields = [
        'attribute_set_id',
        'website_ids',
        'checkout_data',
        'type_id',
        'price',
    ];

    /**
     * Assert form data equals fixture data.
     *
     * @param OrderInjectable $order
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @param FixtureFactory $fixtureFactory
     * @param AssertProductForm $assertProductForm
     * @param AssertConfigurableProductForm $assertConfigurableProductForm
     * @return void
     */
    public function processAssert(
        OrderInjectable $order,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage,
        FixtureFactory $fixtureFactory,
        AssertProductForm $assertProductForm,
        AssertConfigurableProductForm $assertConfigurableProductForm
    ) {
        $productsCount = count($order->getEntityId()['products']);
        for ($i = 0; $i < $productsCount; $i++) {
            $product = $order->getEntityId()['products'][$i];
            $productData = $product->getData();
            if ($product instanceof BundleProduct) {
                $this->assertBundleProduct($product, $productGrid, $productPage, $fixtureFactory, $assertProductForm);
            } elseif ($product instanceof ConfigurableProduct) {
                $assertConfigurableProductForm->processAssert(
                    $fixtureFactory->create(
                        get_class($product),
                        ['data' => array_diff_key($productData, array_flip($this->skipFields))]
                    ),
                    $productGrid,
                    $productPage
                );
            } else {
                $assertProductForm->processAssert(
                    $fixtureFactory->create(
                        get_class($product),
                        ['data' => array_diff_key($productData, array_flip($this->skipFields))]
                    ),
                    $productGrid,
                    $productPage
                );
            }
        }
    }

    /**
     * Assert quantity of products that are part of the bundle product.
     *
     * @param BundleProduct $product
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductEdit $productPage
     * @param FixtureFactory $fixtureFactory
     * @param AssertProductForm $assertProductForm
     * @return void
     */
    public function assertBundleProduct(
        BundleProduct $product,
        CatalogProductIndex $productGrid,
        CatalogProductEdit $productPage,
        FixtureFactory $fixtureFactory,
        AssertProductForm $assertProductForm
    ) {
        $productData = $product->getData();
        $bundleSelections = $product->getDataFieldConfig('bundle_selections')['source']->getProducts();
        foreach ($bundleSelections as $key => $selection) {
            $valueName = $productData['checkout_data']['options']['bundle_options'][$key]['value']['name'];
            foreach ($selection as $item) {
                if (strpos($item->getName(), $valueName) !== false) {
                    $assertProductForm->processAssert(
                        $fixtureFactory->create(
                            get_class($product),
                            ['data' => array_diff_key($item->getData(), array_flip($this->skipFields))]
                        ),
                        $productGrid,
                        $productPage
                    );
                    break;
                }
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products quantity was reverted after order cancel.';
    }
}
