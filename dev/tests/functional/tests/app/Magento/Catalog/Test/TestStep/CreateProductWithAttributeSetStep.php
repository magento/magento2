<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create a new product with the given attribute set.
 */
class CreateProductWithAttributeSetStep implements TestStepInterface
{
    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Catalog Product Index page.
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Catalog Product Edit page.
     *
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * Catalog Product Attribute fixture.
     *
     * @var CatalogProductAttribute
     */
    protected $attribute;

    /**
     * Catalog AttributeSet fixture.
     *
     * @var CatalogAttributeSet
     */
    protected $attributeSet;

    /**
     * Custom attribute value to set while product creation.
     *
     * @var mixed
     */
    protected $attributeValue;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $attributeSet
     * @param mixed $attributeValue [optional]
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $attributeSet,
        $attributeValue = null
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->attribute = $attribute;
        $this->attributeSet = $attributeSet;
        $this->attributeValue = $attributeValue;
    }

    /**
     * Create a new product with the given attribute set
     *
     * @return array
     */
    public function run()
    {
        // Create product with attribute set mentioned above:
        $customAttribute = $this->attribute;
        if ($this->attributeValue !== null) {
            $customAttribute = ['value' => $this->attributeValue, 'attribute' => $customAttribute];
        }
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'product_with_category_with_anchor',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $this->attributeSet],
                    'custom_attribute' => $customAttribute
                ],
            ]
        );
        $this->catalogProductIndex->open()->getGridPageActionBlock()->addProduct('simple');
        $productForm = $this->catalogProductEdit->getProductForm();
        $productForm->fill($product);
        $this->catalogProductEdit->getFormPageActions()->save();

        return ['product' => $product];
    }
}
