<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Mtf\TestStep\TestStepInterface;

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
     * Tab name for adding attribute.
     *
     * @var string
     */
    protected $tabName;

    /**
     * @constructor
     * @param CatalogProductEdit $catalogProductEdit
     * @param string $tabName
     */
    public function __construct(CatalogProductEdit $catalogProductEdit, $tabName)
    {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->tabName = $tabName;
    }

    /**
     * Add custom attribute to product.
     *
     * @return void
     */
    public function run()
    {
        $productForm = $this->catalogProductEdit->getProductForm();
        /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab $productDetailsTab */
        $productForm->addNewAttribute($this->tabName);
    }
}
