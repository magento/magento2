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

use Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductSetIndex;

/**
 * Class AssertProductAttributeAbsenceInTemplateGroups
 * Checks that product attribute isn't displayed in Product template's Groups section
 */
class AssertProductAttributeAbsenceInTemplateGroups extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that deleted attribute isn't displayed in Product template's Groups section
     *
     * @param CatalogAttributeSet $productTemplate
     * @param CatalogProductSetIndex $productSetIndex
     * @param CatalogProductSetEdit $productSetEdit
     * @return void
     */
    public function processAssert(
        CatalogAttributeSet $productTemplate,
        CatalogProductSetIndex $productSetIndex,
        CatalogProductSetEdit $productSetEdit
    ) {
        $filter = ['set_name' => $productTemplate->getAttributeSetName()];
        $productSetIndex->open();
        $productSetIndex->getGrid()->searchAndOpen($filter);

        $attributeCode = $productTemplate
            ->getDataFieldConfig('assigned_attributes')['source']
            ->getAttributes()[0]
            ->getAttributeCode();

        \PHPUnit_Framework_Assert::assertFalse(
            $productSetEdit->getAttributeSetEditBlock()->checkProductAttribute($attributeCode),
            "Attribute " . $attributeCode . " is present in Product template's Groups section."
        );
    }

    /**
     * Text absent Product Attribute in Product template's Groups section
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Product template's Groups section.";
    }
}
