<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Mtf\TestStep\TestStepInterface;

/**
 * Save attribute on attribute page.
 */
class SaveAttributeStep implements TestStepInterface
{
    /**
     * Catalog product attribute edit page.
     *
     * @var CatalogProductAttributeNew
     */
    protected $attributeNew;

    /**
     * @constructor
     * @param CatalogProductAttributeNew $attributeNew
     */
    public function __construct(CatalogProductAttributeNew $attributeNew)
    {
        $this->attributeNew = $attributeNew;
    }

    /**
     * Click "Save" button on attribute edit page.
     *
     * @return void
     */
    public function run()
    {
        $this->attributeNew->getPageActions()->save();
    }
}
