<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Model\Rate;

/**
 * Tax Rate CSV Import Handler
 *
 * @api
 * @since 100.0.2
 */
class CsvImportHandler
{
    /**
     * Collection of publicly available stores
     *
     * @var \Magento\Store\Model\ResourceModel\Store\Collection
     */
    protected $_publicStores;

    /**
     * Region collection prototype
     *
     * The instance is used to retrieve regions based on country code
     *
     * @var \Magento\Directory\Model\ResourceModel\Region\Collection
     */
    protected $_regionCollection;

    /**
     * Country factory
     *
     * @var \Magento\Directory\Model\CountryFactory
     */
    protected $_countryFactory;

    /**
     * Tax rate factory
     *
     * @var \Magento\Tax\Model\Calculation\RateFactory
     */
    protected $_taxRateFactory;

    /**
     * CSV Processor
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @param \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection
     * @param \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Magento\Store\Model\ResourceModel\Store\Collection $storeCollection,
        \Magento\Directory\Model\ResourceModel\Region\Collection $regionCollection,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory,
        \Magento\Framework\File\Csv $csvProcessor
    ) {
        // prevent admin store from loading
        $this->_publicStores = $storeCollection->setLoadDefault(false);
        $this->_regionCollection = $regionCollection;
        $this->_countryFactory = $countryFactory;
        $this->_taxRateFactory = $taxRateFactory;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Retrieve a list of fields required for CSV file (order is important!)
     *
     * @return array
     */
    public function getRequiredCsvFields()
    {
        // indexes are specified for clarity, they are used during import
        return [
            0 => __('Code'),
            1 => __('Country'),
            2 => __('State'),
            3 => __('Zip/Post Code'),
            4 => __('Rate'),
            5 => __('Zip/Post is Range'),
            6 => __('Range From'),
            7 => __('Range To')
        ];
    }

    /**
     * Import Tax Rates from CSV file
     *
     * @param array $file file info retrieved from $_FILES array
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importFromCsvFile($file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
        }
        $ratesRawData = $this->csvProcessor->getData($file['tmp_name']);
        // first row of file represents headers
        $fileFields = $ratesRawData[0];
        $validFields = $this->_filterFileFields($fileFields);
        $invalidFields = array_diff_key($fileFields, $validFields);
        $ratesData = $this->_filterRateData($ratesRawData, $invalidFields, $validFields);
        // store cache array is used to quickly retrieve store ID when handling locale-specific tax rate titles
        $storesCache = $this->_composeStoreCache($validFields);
        $regionsCache = [];
        foreach ($ratesData as $rowIndex => $dataRow) {
            // skip headers
            if ($rowIndex == 0) {
                continue;
            }
            $regionsCache = $this->_importRate($dataRow, $regionsCache, $storesCache);
        }
    }

    /**
     * Filter file fields (i.e. unset invalid fields)
     *
     * @param array $fileFields
     * @return string[] filtered fields
     */
    protected function _filterFileFields(array $fileFields)
    {
        $filteredFields = $this->getRequiredCsvFields();
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $fileFieldsNum = count($fileFields);

        // process title-related fields that are located right after required fields with store code as field name)
        for ($index = $requiredFieldsNum; $index < $fileFieldsNum; $index++) {
            $titleFieldName = $fileFields[$index];
            if ($this->_isStoreCodeValid($titleFieldName)) {
                // if store is still valid, append this field to valid file fields
                $filteredFields[$index] = $titleFieldName;
            }
        }

        return $filteredFields;
    }

    /**
     * Filter rates data (i.e. unset all invalid fields and check consistency)
     *
     * @param array $rateRawData
     * @param array $invalidFields assoc array of invalid file fields
     * @param array $validFields assoc array of valid file fields
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _filterRateData(array $rateRawData, array $invalidFields, array $validFields)
    {
        $validFieldsNum = count($validFields);
        foreach ($rateRawData as $rowIndex => $dataRow) {
            // skip empty rows
            if (count($dataRow) <= 1) {
                unset($rateRawData[$rowIndex]);
                continue;
            }
            // unset invalid fields from data row
            foreach ($dataRow as $fieldIndex => $fieldValue) {
                if (isset($invalidFields[$fieldIndex])) {
                    unset($rateRawData[$rowIndex][$fieldIndex]);
                }
            }
            // check if number of fields in row match with number of valid fields
            if (count($rateRawData[$rowIndex]) != $validFieldsNum) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file format.'));
            }
        }
        return $rateRawData;
    }

    /**
     * Compose stores cache
     *
     * This cache is used to quickly retrieve store ID when handling locale-specific tax rate titles
     *
     * @param string[] $validFields list of valid CSV file fields
     * @return array
     */
    protected function _composeStoreCache($validFields)
    {
        $storesCache = [];
        $requiredFieldsNum = count($this->getRequiredCsvFields());
        $validFieldsNum = count($validFields);
        // title related fields located right after required fields
        for ($index = $requiredFieldsNum; $index < $validFieldsNum; $index++) {
            foreach ($this->_publicStores as $store) {
                $storeCode = $validFields[$index];
                if ($storeCode === $store->getCode()) {
                    $storesCache[$index] = $store->getId();
                }
            }
        }
        return $storesCache;
    }

    /**
     * Check if public store with specified code still exists
     *
     * @param string $storeCode
     * @return boolean
     */
    protected function _isStoreCodeValid($storeCode)
    {
        $isStoreCodeValid = false;
        foreach ($this->_publicStores as $store) {
            if ($storeCode === $store->getCode()) {
                $isStoreCodeValid = true;
                break;
            }
        }
        return $isStoreCodeValid;
    }

    /**
     * Import single rate
     *
     * @param array $rateData
     * @param array $regionsCache cache of regions of already used countries (is used to optimize performance)
     * @param array $storesCache cache of stores related to tax rate titles
     * @return array regions cache populated with regions related to country of imported tax rate
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importRate(array $rateData, array $regionsCache, array $storesCache)
    {
        // data with index 1 must represent country code
        $countryCode = $rateData[1];
        $country = $this->_countryFactory->create()->loadByCode($countryCode, 'iso2_code');
        if (!$country->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('One of the countries has invalid code.'));
        }
        $regionsCache = $this->_addCountryRegionsToCache($countryCode, $regionsCache);

        // data with index 2 must represent region code
        $regionCode = $rateData[2];
        if (!empty($regionsCache[$countryCode][$regionCode])) {
            $regionId = $regionsCache[$countryCode][$regionCode] == '*' ? 0 : $regionsCache[$countryCode][$regionCode];
            // data with index 3 must represent postcode
            $postCode = empty($rateData[3]) ? null : $rateData[3];
            $modelData = [
                'code' => $rateData[0],
                'tax_country_id' => $rateData[1],
                'tax_region_id' => $regionId,
                'tax_postcode' => $postCode,
                'rate' => $rateData[4],
                'zip_is_range' => $rateData[5],
                'zip_from' => $rateData[6],
                'zip_to' => $rateData[7],
            ];

            // try to load existing rate
            /** @var $rateModel \Magento\Tax\Model\Calculation\Rate */
            $rateModel = $this->_taxRateFactory->create()->loadByCode($modelData['code']);
            $rateModel->addData($modelData);

            // compose titles list
            $rateTitles = [];
            foreach ($storesCache as $fileFieldIndex => $storeId) {
                $rateTitles[$storeId] = $rateData[$fileFieldIndex];
            }

            $rateModel->setTitle($rateTitles);
            $rateModel->save();
        }

        return $regionsCache;
    }

    /**
     * Add regions of the given country to regions cache
     *
     * @param string $countryCode
     * @param array $regionsCache
     * @return array
     */
    protected function _addCountryRegionsToCache($countryCode, array $regionsCache)
    {
        if (!isset($regionsCache[$countryCode])) {
            $regionsCache[$countryCode] = [];
            // add 'All Regions' to the list
            $regionsCache[$countryCode]['*'] = '*';
            $regionCollection = clone $this->_regionCollection;
            $regionCollection->addCountryFilter($countryCode);
            if ($regionCollection->getSize()) {
                foreach ($regionCollection as $region) {
                    $regionsCache[$countryCode][$region->getCode()] = $region->getRegionId();
                }
            }
        }
        return $regionsCache;
    }
}
