<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
    public function processAssert(
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
