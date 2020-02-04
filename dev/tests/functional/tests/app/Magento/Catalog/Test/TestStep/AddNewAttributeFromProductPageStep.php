<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Add custom attribute to product from product page.
 */
class AddNewAttributeFromProductPageStep implements TestStepInterface
{
    /**
     * Catalog product index page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * @constructor
     * @param CatalogProductEdit $catalogProductEdit
     */
    public function __construct(CatalogProductEdit $catalogProductEdit)
    {
        $this->catalogProductEdit = $catalogProductEdit;
    }

    /**
     * Add custom attribute to product.
     *
     * @return void
     */
    public function run()
    {
        $productForm = $this->catalogProductEdit->getFormPageActions();
        $productForm->addNewAttribute();
        $this->catalogProductEdit->getAddAttributeModal()->createNewAttribute();
    }
}
