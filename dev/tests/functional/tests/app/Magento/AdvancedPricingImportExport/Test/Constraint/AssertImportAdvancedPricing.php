<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedPricingImportExport\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\ImportExport\Test\Fixture\ImportData;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check imported advanced prices are correct.
 */
class AssertImportAdvancedPricing extends AbstractConstraint
{
    /**
     * Array keys mapping for csv file.
     *
     * @var array
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
     * Edit page on backend.
     *
     * @var CatalogProductEdit
     */
    private $catalogProductEdit;

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
     * @param ImportData $import
     * @return void
     */
    public function processAssert(
        CatalogProductEdit $catalogProductEdit,
        ImportData $import
    ) {
        $this->catalogProductEdit = $catalogProductEdit;
        $this->import = $import;

        $resultArrays = $this->getPreparePrices();

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
    private function getPreparePrices()
    {
        $products = $this->import->getDataFieldConfig('import_file')['source']->getEntities();

        // Prepare tier prices data from page form.
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
        }

        // Prepare tier prices data from csv file.
        $resultCsvArray = [];
        if ($this->import->getBehavior() !== 'Delete') {
            $resultCsvArray = $this->getResultCsv();
        }

        return ['pageData' => $resultProductArray, 'csvData' => $resultCsvArray];
    }

    /**
     * Prepare array from csv file.
     *
     * @return array
     */
    private function getResultCsv()
    {
        $csvData = $this->import->getDataFieldConfig('import_file')['source']->getCsv();

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
