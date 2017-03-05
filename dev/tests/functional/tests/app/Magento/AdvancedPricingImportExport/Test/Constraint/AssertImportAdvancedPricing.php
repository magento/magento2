<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Check imported advanced prices are correct.
 */
class AssertImportAdvancedPricing extends AbstractConstraint
{
    /**
     * Array keys mapping for csv file.
     *
     * @param array
     */
    private $mapping = [
        'sku' => 'sku',
        'tier_price' => 'price',
        'tier_price_qty' => 'price_qty',
        'tier_price_website' => 'website',
        'tier_price_customer_group' => 'customer_group',
        'tier_price_value_type' => 'value_type'
    ];

    /**
     * Csv keys.
     *
     * @param array $csvKeys
     */
    private $csvKeys;

    /**
     * Edit page on backend
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Assert imported advanced prices are correct.
     *
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @param string $behavior
     * @param array $products
     * @param array $csv
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory,
        $behavior,
        array $products,
        array $csv
    ) {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
        $this->prepareCsv($csv);

        $resultArrays = $this->preparePrices($behavior, $products, array_reverse($csv));

        \PHPUnit_Framework_Assert::assertEquals(
            $resultArrays['pageData'],
            $resultArrays['csvData']
        );
    }

    /**
     * Prepare arrays for compare.
     *
     * @param string $behavior
     * @param array $products
     * @param array $csv
     * @return array
     */
    public function preparePrices($behavior, array $products, array $csv)
    {
        $resultProductArray = [];
        for ($i = 0; $i < count($products); $i++) {
            $this->catalogProductEdit->open(['id' => $products[$i]->getId()]);
            $advancedPricing = $this->catalogProductEdit->getProductForm()->openSection('advanced-pricing')
                ->getSection('advanced-pricing');
            $tierPrices = $advancedPricing->getTierPriceForm()->getFieldsData();
            foreach ($tierPrices as $tierPrice) {
                asort($tierPrice);
                $resultProductArray[$products[$i]->getSku()][] = $tierPrice;
            }
        }

        $resultCsvArray = [];
        if ($behavior === 'Delete') {
            $csv = [];
        }
        foreach ($csv as $tierPrice) {
            $tierPrice = array_combine($this->csvKeys, $tierPrice);
            asort($tierPrice);
            $sku = $tierPrice['sku'];
            unset($tierPrice['sku']);
            $resultCsvArray[$sku][] = $tierPrice;
        }

        return ['pageData' => $resultProductArray, 'csvData' => $resultCsvArray];
    }

    /**
     * Prepare assert data.
     *
     * @param array $csv
     * @return array
     */
    private function prepareCsv(array &$csv)
    {
        $this->csvKeys = array_map(
            function ($value) {
                return strtr($value, $this->mapping);
            },
            array_shift($csv)
        ) ;
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Imported advanced prices are correct.';
    }
}
