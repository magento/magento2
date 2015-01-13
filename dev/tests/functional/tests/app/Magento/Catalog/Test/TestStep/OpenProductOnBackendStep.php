<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Fixture\InjectableFixture;
use Mtf\TestStep\TestStepInterface;

/**
 * Open product on backend.
 */
class OpenProductOnBackendStep implements TestStepInterface
{
    /**
     * Product fixture.
     *
     * @var InjectableFixture
     */
    protected $product;

    /**
     * Catalog product index page.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * @constructor
     * @param InjectableFixture $product
     * @param CatalogProductIndex $catalogProductIndex
     */
    public function __construct(InjectableFixture $product, CatalogProductIndex $catalogProductIndex)
    {
        $this->product = $product;
        $this->catalogProductIndex = $catalogProductIndex;
    }

    /**
     * Open products on backend.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->searchAndOpen(['sku' => $this->product->getSku()]);
    }
}
