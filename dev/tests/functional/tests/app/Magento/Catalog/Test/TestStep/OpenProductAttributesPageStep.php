<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Open Product Attribute Index Page.
 */
class OpenProductAttributesPageStep implements TestStepInterface
{
    /**
     * Catalog Product Attribute Index page.
     *
     * @var CatalogProductAttributeIndex
     */
    protected $catalogProductAttributeIndex;

    /**
     * @constructor
     * @param CatalogProductAttributeIndex $catalogProductAttributeIndex
     */
    public function __construct(CatalogProductAttributeIndex $catalogProductAttributeIndex)
    {
        $this->catalogProductAttributeIndex = $catalogProductAttributeIndex;
    }

    /**
     * Open Catalog Product Attribute Index.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductAttributeIndex->open();
    }
}
