<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create product according to data set.
 *
 * Steps:
 * 1. Login to backend.
 * 2. Navigate Products->Catalog.
 * 3. Select products created in preconditions.
 * 4. Select delete from mass-action.
 * 5. Submit form.
 * 6. Perform asserts.
 *
 * @group Products
 * @ZephyrId MAGETWO-23272
 */
class DeleteProductEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Prepare data.
     *
     * @param Category $category
     * @return array
     */
    public function __prepare(Category $category)
    {
        $category->persist();
        return [
            'category' => $category
        ];
    }

    /**
     * Injection data.
     *
     * @param CatalogProductIndex $catalogProductIndexPage
     * @return void
     */
    public function __inject(CatalogProductIndex $catalogProductIndexPage)
    {
        $this->catalogProductIndex = $catalogProductIndexPage;
    }

    /**
     * Run delete product test.
     *
     * @param string $products
     * @param FixtureFactory $fixtureFactory
     * @param Category $category
     * @return array
     */
    public function test($products, FixtureFactory $fixtureFactory, Category $category)
    {
        //Steps
        $products = explode(',', $products);
        $deleteProducts = [];
        foreach ($products as &$product) {
            list($fixture, $dataset) = explode('::', $product);
            $product = $fixtureFactory->createByCode(
                $fixture,
                [
                    'dataset' => $dataset,
                    'data' => [
                        'category_ids' => [
                            'category' => $category,
                        ],
                    ]
                ]
            );
            $product->persist();
            $deleteProducts[] = ['sku' => $product->getSku()];
        }
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->massaction($deleteProducts, 'Delete', true);

        return ['product' => $products];
    }
}
