<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Constraint;

use Magento\Mtf\Util\Command\File\ExportInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\File\Export\Data;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Assert that exported file contains correct product data.
 */
class AssertExportProduct extends AbstractConstraint
{
    /**
     * Assert that exported file contains correct product data.
     *
     * @param ExportInterface $export
     * @param array $exportedFields
     * @param array $products
     * @return void
     */
    public function processAssert(
        ExportInterface $export,
        array $exportedFields,
        array $products
    ) {
        $exportData = $export->getLatest();

        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertTrue(
                $this->isProductDataInFile(
                    $exportedFields,
                    $product,
                    $exportData
                ),
                'Product data was not found in exported file.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product data exists in exported file.';
    }

    /**
     * Get product data from exported file.
     *
     * @param array $fields
     * @param InjectableFixture $product
     * @param Data $exportData
     * @return bool
     */
    private function isProductDataInFile(
        array $fields,
        InjectableFixture $product,
        Data $exportData
    ) {
        $regexp = '/';
        foreach ($fields as $field) {
            if ($field == 'additional_images' && $product->hasData('media_gallery')) {
                $regexp .= '.*(\/?.*(jpg|jpeg|png))';
            } elseif ($field == 'special_price_from_date' && $product->getData('special_price_from_date')) {
                $regexp .= $this->prepareSpecialPriceDateRegexp($product, 'special_price_from_date');
            } elseif ($field == 'special_price_to_date' && $product->getData('special_price_to_date')) {
                $regexp .= $this->prepareSpecialPriceDateRegexp($product, 'special_price_to_date');
            } else {
                $regexp .= '.*(' . $product->getData($field) . ')';
            }
        }
        $regexp .= '/U';

        return (bool) preg_match($regexp, $exportData->getContent());
    }

    /**
     * Prepare special price date field regular expression.
     *
     * @param InjectableFixture $product
     * @param string $field
     * @param string $dateFormat
     * @return string
     */
    private function prepareSpecialPriceDateRegexp(InjectableFixture $product, $field, $dateFormat = 'n/j/y')
    {
        return '.*' . str_replace('/', '\/', date($dateFormat, strtotime($product->getData($field))));
    }
}
