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

namespace Magento\GoogleShopping\Test\Constraint;

use Mtf\Fixture\FixtureFactory;
use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\GoogleShopping\Test\Page\Adminhtml\GoogleShoppingTypesIndex;
use Magento\GoogleShopping\Test\Page\Adminhtml\GoogleShoppingTypesNew;

/**
 * Class AssertProductAttributeAbsenceForAttributeMapping
 * Assert that deleted attribute can't be mapped to Google Attribute
 */
class AssertProductAttributeAbsenceForAttributeMapping extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
                    'attribute_set_id' => ['attribute_set' => $productTemplate]
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
