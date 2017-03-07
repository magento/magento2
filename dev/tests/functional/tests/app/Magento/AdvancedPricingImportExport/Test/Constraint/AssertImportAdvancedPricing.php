<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\ImportExport\Test\Fixture\ImportData;
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
    private $mappingData = [
        'sku' => 'sku',
        'tier_price' => 'price',
        'tier_price_qty' => 'price_qty',
        'tier_price_website' => 'website',
        'tier_price_customer_group' => 'customer_group',
        'tier_price_value_type' => 'value_type'
    ];

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
     * Import fixture.
     *
     * @var ImportData
     */
    private $import;

    /**
     * Assert imported advanced prices are correct.
     *
     * @param CatalogProductEdit $catalogProductEdit
     * @param FixtureFactory $fixtureFactory
     * @param ImportData $import
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $catalogProductEdit,
        FixtureFactory $fixtureFactory,
        ImportData $import
    ) {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->fixtureFactory = $fixtureFactory;
        $this->import = $import;

        $resultArrays = $this->preparePrices();

        \PHPUnit_Framework_Assert::assertEquals(
            $resultArrays['pageData'],
            $resultArrays['csvData'],
            'Tier prices from page and csv are not match.'
        );
    }

    /**
     * Prepare arrays for compare.
     *
     * @return array
     */
    public function preparePrices()
    {
        $products = $this->import->getDataFieldConfig('import_file')['source']->getEntities();

        // Prepare tier prices data from backend.
        $resultProductArray = [];
        foreach ($products as $product) {
            $this->catalogProductEdit->open(['id' => $product->getId()]);
            $advancedPricing = $this->catalogProductEdit->getProductForm()->openSection('advanced-pricing')
                ->getSection('advanced-pricing');
            $tierPrices = $advancedPricing->getTierPriceForm()->getFieldsData();

            $productSku = $product->getSku();

            foreach ($tierPrices as $tierPrice) {
                $resultProductArray[$productSku][] = $tierPrice;
            }
            if (isset($resultProductArray[$productSku])) {
                $resultProductArray[$productSku]= array_reverse($resultProductArray[$productSku]);
            }
        }

        // Prepare tier prices data from csv file.
        $resultCsvArray = [];
        if ($this->import->getBehavior() !== 'Delete') {
            $resultCsvArray = $this->getResultCsv();
        }

        return ['pageData' => $resultProductArray, 'csvData' => $resultCsvArray];
    }

    /**
     * Prepare assert data.
     *
     * @return array
     */
    private function getResultCsv()
    {
        $rowStreamContent = $this->import->getDataFieldConfig('import_file')['source']->getCsv();
        $csvData = array_map(
            function ($value) {
                return explode(',', str_replace('"', '', $value));
            },
            str_getcsv($rowStreamContent, "\n")
        );

        $csvKeys = [];
        foreach (array_shift($csvData) as $csvKey) {
            $csvKeys[] = isset($this->mappingData[$csvKey]) ? $this->mappingData[$csvKey] : $csvKey;
        }

        $resultCsvData = [];
        foreach ($csvData as $csvRowData) {
            $csvRowData = array_combine($csvKeys, $csvRowData);
            $sku = $csvRowData['sku'];
            unset($csvRowData['sku']);
            $resultCsvData[$sku][] = $csvRowData;
        }
        return $resultCsvData;
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
