<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Check whether the attribute is unique.
 */
class AssertProductAttributeIsUnique extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Expected message.
     */
    const UNIQUE_MESSAGE = 'The value of attribute "%s" must be unique';

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Check whether the attribute is unique.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductAttribute $attribute
     * @param CatalogProductSimple $product ,
     * @param FixtureFactory $fixtureFactory
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductAttribute $attribute,
        CatalogProductSimple $product,
        FixtureFactory $fixtureFactory
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $simpleProduct = $this->createSimpleProductFixture($product, $attribute);
        $catalogProductIndex->open()->getGridPageActionBlock()->addProduct('simple');
        $productForm = $catalogProductEdit->getProductForm();
        $productForm->fill($simpleProduct);
        $catalogProductEdit->getFormPageActions()->save();
        $failedAttributes = $productForm->getRequireNoticeAttributes($simpleProduct);
        $attributeLabel = $attribute->getFrontendLabel();
        $actualMessage = $this->getActualMessage($failedAttributes, $attributeLabel);

        \PHPUnit_Framework_Assert::assertEquals(
            sprintf(self::UNIQUE_MESSAGE, $attributeLabel),
            $actualMessage,
            'JS error notice on product edit page is not equal to expected.'
        );
    }

    /**
     * Get actual message.
     *
     * @param array $errors
     * @param string $attributeLabel
     * @return mixed
     */
    protected function getActualMessage(array $errors, $attributeLabel)
    {
        return isset($errors['product-details'][$attributeLabel])
            ? $errors['product-details'][$attributeLabel]
            : null;
    }

    /**
     * Create simple product fixture.
     *
     * @param CatalogProductSimple $product
     * @param CatalogProductAttribute $attribute
     * @return CatalogProductSimple
     */
    protected function createSimpleProductFixture(CatalogProductSimple $product, CatalogProductAttribute $attribute)
    {
        return $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'product_with_category_with_anchor',
                'data' => [
                    'attribute_set_id' => [
                        'attribute_set' => $product->getDataFieldConfig('attribute_set_id')['source']->getAttributeSet()
                    ],
                    'custom_attribute' => $attribute,
                ],
            ]
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Attribute is unique.';
    }
}
