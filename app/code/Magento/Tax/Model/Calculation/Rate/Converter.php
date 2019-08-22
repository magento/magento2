<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Calculation\Rate;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;

/**
 * Tax Rate Model converter.
 *
 * Converts a Tax Rate Model to a Data Object or vice versa.
 */
class Converter
{
    /**
     * @var \Magento\Tax\Api\Data\TaxRateInterfaceFactory
     */
    protected $taxRateDataObjectFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory
     */
    protected $taxRateTitleDataObjectFactory;

    /**
     * @var FormatInterface|null
     */
    private $format;

    /**
     * @param \Magento\Tax\Api\Data\TaxRateInterfaceFactory $taxRateDataObjectFactory
     * @param \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory $taxRateTitleDataObjectFactory
     * @param FormatInterface|null $format
     */
    public function __construct(
        \Magento\Tax\Api\Data\TaxRateInterfaceFactory $taxRateDataObjectFactory,
        \Magento\Tax\Api\Data\TaxRateTitleInterfaceFactory $taxRateTitleDataObjectFactory,
        FormatInterface $format = null
    ) {
        $this->taxRateDataObjectFactory = $taxRateDataObjectFactory;
        $this->taxRateTitleDataObjectFactory = $taxRateTitleDataObjectFactory;
        $this->format = $format ?: ObjectManager::getInstance()->get(FormatInterface::class);
    }

    /**
     * Convert a tax rate data object to an array of associated titles
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @return array
     */
    public function createTitleArrayFromServiceObject(\Magento\Tax\Api\Data\TaxRateInterface $taxRate)
    {
        $titles = $taxRate->getTitles();
        $titleData = [];
        if ($titles) {
            foreach ($titles as $title) {
                $titleData[$title->getStoreId()] = $title->getValue();
            }
        }
        return $titleData;
    }

    /**
     * Extract tax rate data in a format which is
     *
     * @param \Magento\Tax\Api\Data\TaxRateInterface $taxRate
     * @param Boolean $returnNumericLogic
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function createArrayFromServiceObject(
        \Magento\Tax\Api\Data\TaxRateInterface $taxRate,
        $returnNumericLogic = false
    ) {
        $taxRateFormData = [
            'tax_calculation_rate_id' => $taxRate->getId(),
            'tax_country_id' => $taxRate->getTaxCountryId(),
            'tax_region_id' => $taxRate->getTaxRegionId(),
            'tax_postcode' => $taxRate->getTaxPostcode(),
            'code' => $taxRate->getCode(),
            'rate' => $taxRate->getRate(),
            'zip_is_range' => $returnNumericLogic ? 0 : false,
        ];

        if ($taxRateFormData['tax_region_id'] === '0') {
            $taxRateFormData['tax_region_id'] = '';
        }

        if ($taxRate->getZipFrom() && $taxRate->getZipTo()) {
            $taxRateFormData['zip_is_range'] = $returnNumericLogic ? 1 : true;
            $taxRateFormData['zip_from'] = $taxRate->getZipFrom();
            $taxRateFormData['zip_to'] = $taxRate->getZipTo();
        }

        if ($returnNumericLogic) {
            //format for the ajax on multiple sites titles
            $titleArray=($this->createTitleArrayFromServiceObject($taxRate));
            if (is_array($titleArray)) {
                foreach ($titleArray as $storeId => $title) {
                    $taxRateFormData['title[' . $storeId . ']']=$title;
                }
            }
        } else {
            //format for the form array on multiple sites titles
            $titleArray=($this->createTitleArrayFromServiceObject($taxRate));
            if (is_array($titleArray)) {
                $titleData = [];
                foreach ($titleArray as $storeId => $title) {
                    $titleData[] = [$storeId => $title];
                }
                if (count($titleArray)>0) {
                    $taxRateFormData['title'] = $titleData;
                }
            }
        }

        return $taxRateFormData;
    }

    /**
     * Convert an array to a tax rate data object
     *
     * @param array $formData
     * @return \Magento\Tax\Api\Data\TaxRateInterface
     */
    public function populateTaxRateData($formData)
    {
        $taxRate = $this->taxRateDataObjectFactory->create();
        $taxRate->setId($this->extractFormData($formData, 'tax_calculation_rate_id'))
            ->setTaxCountryId($this->extractFormData($formData, 'tax_country_id'))
            ->setTaxRegionId($this->extractFormData($formData, 'tax_region_id'))
            ->setTaxPostcode($this->extractFormData($formData, 'tax_postcode'))
            ->setCode($this->extractFormData($formData, 'code'))
            ->setRate($this->format->getNumber($this->extractFormData($formData, 'rate')));
        if (isset($formData['zip_is_range']) && $formData['zip_is_range']) {
            $taxRate->setZipFrom($this->extractFormData($formData, 'zip_from'))
                ->setZipTo($this->extractFormData($formData, 'zip_to'))->setZipIsRange(1);
        }

        if (isset($formData['title'])) {
            $titles = [];
            foreach ($formData['title'] as $storeId => $value) {
                $titles[] = $this->taxRateTitleDataObjectFactory->create()->setStoreId($storeId)->setValue($value);
            }
            $taxRate->setTitles($titles);
        }

        return $taxRate;
    }

    /**
     * Determines if an array value is set in the form data array and returns it.
     *
     * @param array $formData the form to get data from
     * @param string $fieldName the key
     * @return null|string
     */
    protected function extractFormData($formData, $fieldName)
    {
        if (isset($formData[$fieldName])) {
            return $formData[$fieldName];
        }
        return null;
    }
}
