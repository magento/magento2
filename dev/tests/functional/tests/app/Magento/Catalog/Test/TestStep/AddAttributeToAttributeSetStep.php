<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Magento\Mtf\Fixture\FixtureFactory;
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
     * Custom attribute value to set while product creation.
     *
     * @var mixed
     */
    protected $attributeValue;

    /**
     * @constructor
     * @param CatalogProductSetIndex $catalogProductSetIndex
     * @param CatalogProductSetEdit $catalogProductSetEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $attributeSet
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param mixed $attributeValue [optional]
     */
    public function __construct(
        CatalogProductSetIndex $catalogProductSetIndex,
        CatalogProductSetEdit $catalogProductSetEdit,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $attributeSet,
        FixtureFactory $fixtureFactory,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        $attributeValue = null
    ) {
        $this->catalogProductSetIndex = $catalogProductSetIndex;
        $this->catalogProductSetEdit = $catalogProductSetEdit;
        $this->attribute = $attribute;
        $this->attributeSet = $attributeSet;
        $this->fixtureFactory = $fixtureFactory;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
        $this->attributeValue = $attributeValue;
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
        $this->catalogProductSetEdit->getAttributeSetEditBlock()->moveAttribute($this->attribute->getData());
        $this->catalogProductSetEdit->getPageActions()->save();

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
