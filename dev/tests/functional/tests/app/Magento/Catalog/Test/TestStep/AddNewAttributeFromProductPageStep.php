<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Client\BrowserInterface;

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
     * Browser instance.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * Add Attribute modal.
     *
     * @var string
     */
    protected $addAttributeModal = '.product_form_product_form_add_attribute_modal';

    /**
     * "Create New Attribute" button.
     *
     * @var string
     */
    protected $createNewAttribute = 'button[data-index="add_new_attribute_button"]';

    /**
     * @constructor
     * @param CatalogProductEdit $catalogProductEdit
     * @param BrowserInterface $browser
     */
    public function __construct(CatalogProductEdit $catalogProductEdit, BrowserInterface $browser)
    {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->browser = $browser;
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
        $this->browser->find($this->addAttributeModal)->find($this->createNewAttribute)->click();
    }
}
