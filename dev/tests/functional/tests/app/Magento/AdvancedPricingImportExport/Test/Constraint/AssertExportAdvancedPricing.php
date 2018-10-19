<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Util\Command\File\ExportInterface;

/**
 * Assert that exported file with advanced pricing options contains product data.
 */
class AssertExportAdvancedPricing extends AbstractConstraint
{
    /**
     * Export data.
     *
     * @var array
     */
    private $exportData;

    /**
     * Assert that exported file with advanced pricing options contains product data.
     *
     * @param ExportInterface $export
     * @param array $products
     * @param array $exportedFields
     * @return void
     */
    public function processAssert(
        ExportInterface $export,
        array $products,
        array $exportedFields
    ) {
        $this->exportData = $export->getLatest();
        foreach ($products as $product) {
            $regexps = $this->prepareRegexpsForCheck($exportedFields, $product);
            \PHPUnit\Framework\Assert::assertTrue(
                $this->isProductDataExists($regexps),
                'A product with name ' . $product->getName() . ' was not found in exported file.'
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
     * Prepare regular expressions for product data in exported file.
     *
     * @param array $fields
     * @param InjectableFixture $product
     * @return array
     */
    private function prepareRegexpsForCheck(
        array $fields,
        InjectableFixture $product
    ) {
        $regexpsForCheck = [];
        $tierPrices = count($product->getData()['tier_price']);
        for ($i = 0; $i < $tierPrices; $i++) {
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
            $regexp .= '/U';

            $regexpsForCheck[] = $regexp;
        }

        return $regexpsForCheck;
    }

    /**
     * Check product data existing in exported file.
     *
     * @param array $data
     * @return bool
     */
    private function isProductDataExists(array $data)
    {
        foreach ($data as $regexp) {
            preg_match($regexp, $this->exportData->getContent(), $matches);
            if (empty($matches)) {
                return false;
            }
        }

        return true;
    }
}
