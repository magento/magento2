<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShopping\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\GoogleShopping\Test\Page\Adminhtml\GoogleShoppingTypesIndex;
use Magento\GoogleShopping\Test\Page\Adminhtml\GoogleShoppingTypesNew;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertProductAttributeAbsenceForAttributeMapping
 * Assert that deleted attribute can't be mapped to Google Attribute
 */
class AssertProductAttributeAbsenceForAttributeMapping extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that deleted attribute can't be mapped to Google Attribute (attribute doesn't appear in Attributes
     * Mapping -> Google Content - Attributes after selecting attribute set)
     *
     * @param FixtureFactory $fixtureFactory
     * @param CatalogAttributeSet $productTemplate
     * @param GoogleShoppingTypesIndex $shoppingTypesIndex
     * @param GoogleShoppingTypesNew $shoppingTypesNew
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CatalogAttributeSet $productTemplate,
        GoogleShoppingTypesIndex $shoppingTypesIndex,
        GoogleShoppingTypesNew $shoppingTypesNew
    ) {
        $shoppingTypesIndex->open();
        $shoppingTypesIndex->getPageActionsBlock()->addNew();

        $shoppingAttributes = $fixtureFactory->createByCode(
            'googleShoppingAttribute',
            [
                'dataSet' => 'default',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $productTemplate],
                ],
            ]
        );

        $shoppingTypesNew->getGoogleShoppingForm()->fill($shoppingAttributes);
        $shoppingTypesNew->getGoogleShoppingForm()->clickAddNewAttribute();

        $attributeCode = $productTemplate
            ->getDataFieldConfig('assigned_attributes')['source']
            ->getAttributes()[0]
            ->getAttributeCode();

        \PHPUnit_Framework_Assert::assertFalse(
            $shoppingTypesNew->getGoogleShoppingForm()->findAttribute($attributeCode),
            "Attribute " . $attributeCode . " is present in Attribute set mapping"
        );
    }

    /**
     * Text absent Product Attribute in Google Content Attribute Mapping
     *
     * @return string
     */
    public function toString()
    {
        return "Attribute is absent in Google Content Attribute Mapping.";
    }
}
