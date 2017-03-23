<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Add New Attribute from Attribute index page.
 */
class AddNewAttributeStep implements TestStepInterface
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
     * Add New Attribute from Attribute index page step.
     *
     * @return void
     */
    public function run()
    {
        $this->catalogProductAttributeIndex->getPageActionsBlock()->addNew();
    }
}
