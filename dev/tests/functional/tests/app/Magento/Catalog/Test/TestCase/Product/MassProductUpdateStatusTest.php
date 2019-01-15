<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\TestCase\Product;

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
 * 4. Select Change status action from mass-action.
 * 5. Select Disable
 * 6. Perform asserts.
 *
 * @group Products
 * @ZephyrId MAGETWO-60847
 */
class MassProductUpdateStatusTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Product page with the grid.
     *
     * @var CatalogProductIndex
     */
    private $catalogProductIndex;

    /**
     * Product grid action
     *
     * @var string
     */
    private $productGridAction = 'Change status';

    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Injection data
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param FixtureFactory $fixtureFactory
     */
    public function __inject(
        CatalogProductIndex $catalogProductIndex,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductIndex = $catalogProductIndex;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Mass update the status of the products in the grid
     *
     * @param  string $gridStatus
     * @param array $initialProducts
     * @return array
     */
    public function test(
        $gridStatus,
        array $initialProducts,
        FixtureFactory $fixtureFactory
    ) {
        // Preconditions
        $changeStatusProducts = [];
        foreach ($initialProducts as $product) {
            list($fixture, $dataset) = explode('::', $product);
            $product = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
            $product->persist();
            $changeStatusProducts[] = ['sku' => $product->getSku()];
        }

        // Steps
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()
             ->massaction($changeStatusProducts, [$this->productGridAction => $gridStatus]);
        return ['products' => $initialProducts];
    }
}
