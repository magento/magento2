<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Set default attribute value.
 */
class SetDefaultAttributeValueStep implements TestStepInterface
{
    /**
     * CatalogProductAttribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * FixtureFactory object.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog product index page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Custom attribute value to set while product creation.
     *
     * @var string
     */
    protected $attributeValue;

    /**
     * @constructor
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @param string $attributeValue [optional]
     */
    public function __construct(
        CatalogProductAttribute $attribute,
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory,
        $attributeValue = null
    ) {
        $this->attribute = $attribute;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
        $this->attributeValue = $attributeValue;
    }

    /**
     * Set Default Attribute Value.
     *
     * @return void
     */
    public function run()
    {
        $customAttribute = $this->attribute;
        if ($this->attributeValue !== null) {
            $customAttribute = ['value' => $this->attributeValue, 'attribute' => $customAttribute];
        }
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => ['custom_attribute' => $customAttribute]]
        );
        $this->catalogProductEdit->getProductForm()->fill($product);
    }
}
