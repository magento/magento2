<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Constraint;

use Mtf\Fixture\FixtureFactory;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Class AssertProductTemplateOnProductForm
 * Check Attribute Set and Product Attribute on Product form
 */
class AssertProductTemplateOnProductForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that created product template:
     * 1. Displays in product template suggest container dropdown
     * 2. Can be used for new created product.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductEdit $productEdit
     * @param CatalogProductIndex $productGrid
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogAttributeSet $attributeSetOriginal
     * @param CatalogProductNew $newProductPage
     * @param CatalogProductAttribute $productAttribute
     * @return void
     */
    public function processAssert
    (
        FixtureFactory $fixtureFactory,
        CatalogProductEdit $productEdit,
        CatalogProductIndex $productGrid,
        CatalogAttributeSet $attributeSet,
        CatalogProductNew $newProductPage,
        CatalogProductAttribute $productAttribute,
        CatalogAttributeSet $attributeSetOriginal = null
    ) {

        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $productBlockForm = $newProductPage->getProductForm();

        /**@var CatalogProductSimple $catalogProductSimple */
        $productSimple = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'default',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $attributeSet],
                ],
            ]
        );
        $productBlockForm->fill($productSimple);
        $newProductPage->getFormPageActions()->save();

        $formData = $productEdit->getProductForm()->getData($productSimple);
        $formAttributeSet = $formData['attribute_set_id'];
        \PHPUnit_Framework_Assert::assertEquals(
            $attributeSet->getAttributeSetName(),
            $formAttributeSet,
            'Attribute Set not found on Product form.'
            . "\nExpected: " . $attributeSet->getAttributeSetName()
            . "\nActual: " . $formAttributeSet
        );

        if ($attributeSetOriginal === null) {
            $productEdit->getProductForm()->openTab('product-details');

            \PHPUnit_Framework_Assert::assertTrue(
                $productEdit->getProductForm()->checkAttributeLabel($productAttribute),
                "Product Attribute is absent on Product form."
            );
        }
    }

    /**
     * Text of Product Attribute and Attribute Set are present on the Product form.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute and Attribute Set are present on the Product form.';
    }
}
