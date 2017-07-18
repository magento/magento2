<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\TestCase\Injectable;

/**
 * Base class for promoted products.
 */
abstract class AbstractProductPromotedProductsTest extends Injectable
{
    /**
     * Interface Browser.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Fixture Factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Suite products.
     *
     * @var InjectableFixture[]
     */
    protected $products = [];

    /**
     * Catalog product index page in backend.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog product edit page in backend.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Catalog product view page in frontend.
     *
     * @var CatalogProductView
     */
    protected $catalogProductView;

    /**
     * Prepare data.
     *
     * @param BrowserInterface $browser
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(BrowserInterface $browser, FixtureFactory $fixtureFactory)
    {
        $this->browser = $browser;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductView $catalogProductView
     * @return void
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductView $catalogProductView
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->catalogProductView = $catalogProductView;
    }

    /**
     * Create products.
     *
     * @param string $products
     * @return void
     */
    protected function createProducts($products)
    {
        $list = array_map('trim', explode(',', $products));

        foreach ($list as $item) {
            list($productName, $fixtureCode, $dataset) = array_map('trim', explode('::', $item));
            $product = $this->fixtureFactory->createByCode($fixtureCode, ['dataset' => $dataset]);

            $product->persist();
            $this->products[$productName] = $product;
        }
    }

    /**
     * Assign promoted products.
     *
     * @param string $promotedProducts
     * @param string $type
     * @return void
     */
    protected function assignPromotedProducts($promotedProducts, $type)
    {
        $promotedProducts = $this->parsePromotedProducts($promotedProducts);

        foreach ($promotedProducts as $productName => $assignedNames) {
            $initialProduct = $this->products[$productName];
            $filter = ['sku' => $initialProduct->getSku()];
            $assignedProducts = [];

            foreach ($assignedNames as $assignedName) {
                $assignedProducts[] = $this->products[$assignedName];
            }

            $product = $this->fixtureFactory->create(
                get_class($initialProduct),
                [
                    'data' => [
                        $type => [
                            'products' => $assignedProducts
                        ]
                    ]
                ]
            );
            $this->catalogProductIndex->open();
            $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
            $this->catalogProductEdit->getProductForm()->fill($product);
            $this->catalogProductEdit->getFormPageActions()->save();
            $this->catalogProductEdit->getMessagesBlock()->waitSuccessMessage();
        }
    }

    /**
     * Parse promoted products.
     *
     * @param string $promotedProducts
     * @return array
     */
    protected function parsePromotedProducts($promotedProducts)
    {
        $list = array_map('trim', explode(';', $promotedProducts));
        $result = [];

        foreach ($list as $item) {
            list($productName, $promotedNames) = array_map('trim', explode(':', $item));
            $result[$productName] = array_map('trim', explode(',', $promotedNames));
        }

        return $result;
    }

    /**
     * Convert list of navigate products to array.
     *
     * @param string $navigateProductsOrder
     * @return array
     */
    protected function parseNavigateProductsOrder($navigateProductsOrder)
    {
        return array_map('trim', explode(',', $navigateProductsOrder));
    }

    /**
     * Convert products to verify data to array.
     *
     * @param string $productsToVerify
     * @return array
     */
    protected function parseProductsToVerify($productsToVerify)
    {
        $result = [];
        $list = array_map('trim', explode(';', $productsToVerify));

        foreach ($list as $item) {
            list($step, $products) = array_map('trim', explode(':', $item));
            $result[$step] = empty($products)
                ? []
                : array_map('trim', explode(',', $products));
        }

        return $result;
    }
}
