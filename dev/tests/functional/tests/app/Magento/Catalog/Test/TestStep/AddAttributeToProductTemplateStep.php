<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;
use Mtf\Fixture\FixtureFactory;
use Mtf\TestStep\TestStepInterface;

/**
 * Move attribute To attribute set.
 */
class AddAttributeToProductTemplateStep implements TestStepInterface
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
    protected $productTemplate;

    /**
     * @constructor
     * @param CatalogProductSetIndex $catalogProductSetIndex
     * @param CatalogProductSetEdit $catalogProductSetEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogAttributeSet $productTemplate
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(
        CatalogProductSetIndex $catalogProductSetIndex,
        CatalogProductSetEdit $catalogProductSetEdit,
        CatalogProductAttribute $attribute,
        CatalogAttributeSet $productTemplate,
        FixtureFactory $fixtureFactory
    ) {
        $this->catalogProductSetIndex = $catalogProductSetIndex;
        $this->catalogProductSetEdit = $catalogProductSetEdit;
        $this->attribute = $attribute;
        $this->productTemplate = $productTemplate;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Move attribute To attribute set.
     *
     * @return array
     */
    public function run()
    {
        $filterAttribute = ['set_name' => $this->productTemplate->getAttributeSetName()];
        $this->catalogProductSetIndex->open()->getGrid()->searchAndOpen($filterAttribute);
        $this->catalogProductSetEdit->getAttributeSetEditBlock()->moveAttribute($this->attribute->getData());
        $this->catalogProductSetEdit->getPageActions()->save();

        // Create product with attribute set mentioned above:
        $product = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'product_with_category_with_anchor',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $this->productTemplate],
                    'custom_attribute' => $this->attribute,
                ],
            ]
        );
        $product->persist();

        return ['product' => $product];
    }
}
