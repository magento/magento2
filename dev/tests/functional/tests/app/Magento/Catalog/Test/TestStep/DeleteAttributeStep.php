<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductAttributeNew;
use Mtf\TestStep\TestStepInterface;

/**
 * Delete product attribute.
 */
class DeleteAttributeStep implements TestStepInterface
{
    /**
     * Catalog Product Attribute Index page.
     *
     * @var CatalogProductAttributeIndex
     */
    protected $catalogProductAttributeIndex;

    /**
     * Catalog Product Attribute New page.
     *
     * @var CatalogProductAttributeNew
     */
    protected $catalogProductAttributeNew;

    /**
     * CatalogProductAttribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * @constructor
     * @param CatalogProductAttributeIndex $catalogProductAttributeIndex
     * @param CatalogProductAttributeNew $catalogProductAttributeNew
     * @param CatalogProductAttribute $attribute
     */
    public function __construct(
        CatalogProductAttributeIndex $catalogProductAttributeIndex,
        CatalogProductAttributeNew $catalogProductAttributeNew,
        CatalogProductAttribute $attribute
    ) {
        $this->catalogProductAttributeIndex = $catalogProductAttributeIndex;
        $this->catalogProductAttributeNew = $catalogProductAttributeNew;
        $this->attribute = $attribute;
    }

    /**
     * Delete product attribute step.
     *
     * @return void
     */
    public function run()
    {
        $filter = ['attribute_code' => $this->attribute->getAttributeCode()];
        if ($this->catalogProductAttributeIndex->open()->getGrid()->isRowVisible($filter)) {
            $this->catalogProductAttributeIndex->getGrid()->searchAndOpen($filter);
            $this->catalogProductAttributeNew->getPageActions()->delete();
        }
    }
}
