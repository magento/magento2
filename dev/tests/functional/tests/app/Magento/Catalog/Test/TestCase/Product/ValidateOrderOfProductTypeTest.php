<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestCase\Product;

use Magento\Mtf\TestCase\Injectable;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Steps:
 * 1. Login to backend.
 * 2. Navigate to PRODUCTS -> Catalog.
 *
 * @group Products
 * @ZephyrId MAGETWO-37146
 */
class ValidateOrderOfProductTypeTest extends Injectable
{
    /* tags */
    const MVP = 'no';
    /* end tags */

    /**
     * Open catalog product index page.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @return void
     */
    public function test(CatalogProductIndex $catalogProductIndex)
    {
        $catalogProductIndex->open();
    }
}
