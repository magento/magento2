<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Mtf\TestStep\TestStepInterface;

/**
 * Fill custom attribute form on product page.
 */
class FillAttributeFormOnProductPageStep implements TestStepInterface
{
    /**
     * CatalogProductAttribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Catalog product edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * @constructor
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductEdit $catalogProductEdit
     */
    public function __construct(CatalogProductAttribute $attribute, CatalogProductEdit $catalogProductEdit)
    {
        $this->attribute = $attribute;
        $this->catalogProductEdit = $catalogProductEdit;
    }

    /**
     * Fill custom attribute form on product page.
     *
     * @return array
     */
    public function run()
    {
        $this->catalogProductEdit->getProductForm()->fillAttributeForm($this->attribute);
        return ['attribute' => $this->attribute];
    }
}
