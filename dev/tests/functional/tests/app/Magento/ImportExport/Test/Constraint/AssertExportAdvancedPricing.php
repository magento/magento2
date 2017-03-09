<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\File\Export\Data;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Util\Command\File\Export;

/**
 * Assert that exported file with advanced pricing options contains product data.
 */
class AssertExportAdvancedPricing extends AbstractConstraint
{
    /**
     * Assert that exported file with advanced pricing options contains product data.
     *
     * @param Export $export
     * @param array $products
     * @param array $exportedFields
     * @param array|null $advancedPricingAttributes
     * @return void
     */
    public function processAssert(
        Export $export,
        array $products,
        array $exportedFields,
        array $advancedPricingAttributes = []
    ) {
        $exportData = $export->getLatest();

        if (!empty($advancedPricingAttributes)) {
            $products = [$products[$advancedPricingAttributes['product']]];
        }

        foreach ($products as $product) {
            \PHPUnit_Framework_Assert::assertTrue(
                $this->isProductDataInFile(
                    $exportedFields,
                    $product,
                    $exportData
                ),
                "A product with name '" . $product->getName() . "' was not found in exported file."
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
        return 'A product(s) with correct data was found in exported file.';
    }

    /**
     * Get product data from exported file.
     *
     * @param array $fields
     * @param InjectableFixture $product
     * @param Data $exportData
     * @param string $quantifiers
     * @return void
     */
    public function isProductDataInFile(
        array $fields,
        InjectableFixture $product,
        Data $exportData,
        $quantifiers = 'U'
    ) {
        $dataForCheck = [];
        for ($i = 0; $i < count($product->getData()['tier_price']); $i++) {
            $regexp = '/';
            foreach ($fields as $field) {
                if (strpos($field, 'tier_price') !== false) {
                    $replace = ($field == 'tier_price' || $field == 'tier_price_qty') ? 'tier_' : 'tier_price_';
                    $regexp .= preg_replace(
                        '/[\[\]]/',
                        '.*',
                        '.*(' . $product->getData()['tier_price'][$i][str_replace($replace, '', $field)] . ')'
                    );
                } else {
                    $regexp .= '.*(' . $product->getData($field) . ').*';
                }
            }
            $regexp .= '/' . $quantifiers;

            $dataForCheck[] = $regexp;
        }
        foreach ($dataForCheck as $regexp) {
            preg_match($regexp, $exportData->getContent(), $matches);
            if (empty($matches)) {
                return false;
            }
        }
        return true;
    }
}
