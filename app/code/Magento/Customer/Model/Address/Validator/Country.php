<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Escaper;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Address country and region validator.
 */
class Country implements ValidatorInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Data
     */
    private $directoryData;

    /**
     * @var AllowedCountries
     */
    private $allowedCountriesReader;

    /**
     * @param Data $directoryData
     * @param AllowedCountries $allowedCountriesReader
     * @param Escaper|null $escaper
     */
    public function __construct(
        Data $directoryData,
        AllowedCountries $allowedCountriesReader,
        Escaper $escaper = null
    ) {
        $this->directoryData = $directoryData;
        $this->allowedCountriesReader = $allowedCountriesReader;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
    }

    /**
     * @inheritdoc
     */
    public function validate(AbstractAddress $address)
    {
        $errors = $this->validateCountry($address);
        if (empty($errors)) {
            $errors = $this->validateRegion($address);
        }

        return $errors;
    }

    /**
     * Validate country existence.
     *
     * @param AbstractAddress $address
     * @return array
     */
    private function validateCountry(AbstractAddress $address)
    {
        $countryId = $address->getCountryId();
        $errors = [];
        if (!\Zend_Validate::is($countryId, 'NotEmpty')) {
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'countryId']);
        } elseif (!in_array($countryId, $this->getWebsiteAllowedCountries($address), true)) {
            //Checking if such country exists.
            $errors[] = __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'countryId', 'value' => $this->escaper->escapeHtml($countryId)]
            );
        }

        return $errors;
    }

    /**
     * Validate region existence.
     *
     * @param AbstractAddress $address
     * @return array
     * @throws \Zend_Validate_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateRegion(AbstractAddress $address)
    {
        $errors = [];
        $countryId = $address->getCountryId();
        $countryModel = $address->getCountryModel();
        $regionCollection = $countryModel->getRegionCollection();
        $region = $address->getRegion();
        $regionId = (string)$address->getRegionId();
        $allowedRegions = $regionCollection->getAllIds();
        $isRegionRequired = $this->directoryData->isRegionRequired($countryId);
        if ($isRegionRequired && empty($allowedRegions) && !\Zend_Validate::is($region, 'NotEmpty')) {
            //If region is required for country and country doesn't provide regions list
            //region must be provided.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'region']);
        } elseif ($allowedRegions && !\Zend_Validate::is($regionId, 'NotEmpty') && $isRegionRequired) {
            //If country actually has regions and requires you to
            //select one then it must be selected.
            $errors[] = __('"%fieldName" is required. Enter and try again.', ['fieldName' => 'regionId']);
        } elseif ($allowedRegions && $regionId && !in_array($regionId, $allowedRegions, true)) {
            //If a region is selected then checking if it exists.
            $errors[] = __(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'regionId', 'value' => $this->escaper->escapeHtml($regionId)]
            );
        }

        return $errors;
    }

    /**
     * Return allowed counties per website.
     *
     * @param AbstractAddress $address
     * @return array
     */
    private function getWebsiteAllowedCountries(AbstractAddress $address): array
    {
        $storeId = $address->getData('store_id');
        return $this->allowedCountriesReader->getAllowedCountries(ScopeInterface::SCOPE_STORE, $storeId);
    }
}
