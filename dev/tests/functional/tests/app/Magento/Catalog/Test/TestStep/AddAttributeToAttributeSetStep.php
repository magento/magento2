<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Move attribute To attribute set.
 */
class AddAttributeToAttributeSetStep implements TestStepInterface
{
    /**
     * Catalog ProductSet Index page.
     *
     * @var CatalogProductSetIndex
     */
    protected $catalogProductSetIndex;

    /**
     * Catalog ProductSet Edit page.
     *
     * @var CatalogProductSetEdit
     */
    protected $catalogProductSetEdit;

    /**
     * Catalog Product Attribute fixtures.
     *
     * @var CatalogProductAttribute[]
     */
    protected $attribute;

    /**
     * Catalog AttributeSet fixture.
     *
     * @var CatalogAttributeSet
     */
    protected $attributeSet;

    /**
     * @constructor
     * @param CatalogProductSetIndex $catalogProductSetIndex
     * @param CatalogProductSetEdit $catalogProductSetEdit
     * @param CatalogProductAttribute|array $attribute
     * @param CatalogAttributeSet $attributeSet
     */
    public function __construct(
        CatalogProductSetIndex $catalogProductSetIndex,
        CatalogProductSetEdit $catalogProductSetEdit,
        $attribute,
        CatalogAttributeSet $attributeSet
    ) {
        $this->catalogProductSetIndex = $catalogProductSetIndex;
        $this->catalogProductSetEdit = $catalogProductSetEdit;
        if (!is_array($attribute)) {
            $this->attribute = [$attribute];
        } else {
            $this->attribute = $attribute;
        }
        $this->attributeSet = $attributeSet;
    }

    /**
     * Move attribute To attribute set.
     *
     * @return array
     */
    public function run()
    {
        $filterAttribute = ['set_name' => $this->attributeSet->getAttributeSetName()];
        $this->catalogProductSetIndex->open()->getGrid()->searchAndOpen($filterAttribute);
        foreach ($this->attribute as $attribute) {
            $this->catalogProductSetEdit->getAttributeSetEditBlock()->moveAttribute($attribute->getData());
        }
        $this->catalogProductSetEdit->getPageActions()->save();
    }
}
