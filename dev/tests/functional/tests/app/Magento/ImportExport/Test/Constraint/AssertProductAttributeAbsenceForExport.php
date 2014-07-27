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

namespace Magento\ImportExport\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\ImportExport\Test\Fixture\ImportExport;
use Magento\ImportExport\Test\Page\Adminhtml\AdminExportIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAbsenceProductAttributeForExport
 * Checks that product attribute cannot be used for Products' Export
 */
class AssertProductAttributeAbsenceForExport extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that deleted attribute can't be used for Products' Export
     *
     * @param AdminExportIndex $exportIndex
     * @param CatalogProductAttribute $attribute
     * @param ImportExport $export
     * @return void
     */
    public function processAssert
    (
        AdminExportIndex $exportIndex,
        CatalogProductAttribute $attribute,
        ImportExport $export
    ) {
        $exportIndex->open();
        $exportIndex->getExportForm()->fill($export);

        $filter = [
            'attribute_code' => $attribute->getAttributeCode(),
        ];

        \PHPUnit_Framework_Assert::assertFalse(
            $exportIndex->getFilterExport()->isRowVisible($filter),
            'Attribute \'' . $attribute->getFrontendLabel() . '\' is present in Filter export grid'
        );
    }

    /**
     * Text absent Product Attribute in Filter export grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Attribute is absent in Filter export grid';
    }
}
