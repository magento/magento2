<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Check whether the attribute is unique.
 */
class AssertProductAttributeIsUnique extends AbstractConstraint
{
    /**
     * Expected message.
     */
    const UNIQUE_MESSAGE = 'The value of attribute "%s" must be unique';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Check whether the attribute is unique.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     * @param CatalogProductSimple $product
     * @param CatalogProductAttribute $attribute
     * @throws \Exception
     * @return void
     */
    public function processAssert(
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit,
        CatalogProductSimple $product,
        CatalogProductAttribute $attribute
    ) {
        $catalogProductIndex->open()->getGridPageActionBlock()->addProduct('simple');
        $productForm = $catalogProductEdit->getProductForm();
        $productForm->fill($product);
        $catalogProductEdit->getFormPageActions()->save();
        $failedAttributes = $productForm->getRequireNoticeAttributes($product);
        $actualMessage = $failedAttributes['product-details'][$attribute->getFrontendLabel()];

        $fixtureData = $attribute->getData();
        $defaultValue = preg_grep('/^default_value/', array_keys($fixtureData));

        \PHPUnit_Framework_Assert::assertEquals(
            self::UNIQUE_MESSAGE,
            sprintf($actualMessage, $fixtureData[array_shift($defaultValue)]),
            'JS error notice on product edit page is not equal to expected.'
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
