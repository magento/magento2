<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject;
use Magento\Store\Model\Address\Renderer;

/**
 * Store information class used to retrieve and format store information as set in store config
 *
 * @api
 */
class Information
{
    /**#@+
     * Configuration paths
     */
    const XML_PATH_STORE_INFO_NAME = 'general/store_information/name';

    const XML_PATH_STORE_INFO_PHONE = 'general/store_information/phone';

    const XML_PATH_STORE_INFO_HOURS = 'general/store_information/hours';

    const XML_PATH_STORE_INFO_STREET_LINE1 = 'general/store_information/street_line1';

    const XML_PATH_STORE_INFO_STREET_LINE2 = 'general/store_information/street_line2';

    const XML_PATH_STORE_INFO_CITY = 'general/store_information/city';

    const XML_PATH_STORE_INFO_POSTCODE = 'general/store_information/postcode';

    const XML_PATH_STORE_INFO_REGION_CODE = 'general/store_information/region_id';

    const XML_PATH_STORE_INFO_COUNTRY_CODE = 'general/store_information/country_id';

    const XML_PATH_STORE_INFO_VAT_NUMBER = 'general/store_information/merchant_vat_number';
    /**#@-*/

    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @param Renderer $renderer
     * @param RegionFactory $regionFactory
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        Renderer $renderer,
        RegionFactory $regionFactory,
        CountryFactory $countryFactory
    ) {
        $this->renderer = $renderer;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
    }

    /**
     * Retrieve generic object with all the misc store information values
     *
     * @param Store $store
     * @return DataObject
     */
    public function getStoreInformationObject(Store $store)
    {
        $info = new DataObject([
            'name' => $store->getConfig(self::XML_PATH_STORE_INFO_NAME),
            'phone' => $store->getConfig(self::XML_PATH_STORE_INFO_PHONE),
            'hours' => $store->getConfig(self::XML_PATH_STORE_INFO_HOURS),
            'street_line1' => $store->getConfig(self::XML_PATH_STORE_INFO_STREET_LINE1),
            'street_line2' => $store->getConfig(self::XML_PATH_STORE_INFO_STREET_LINE2),
            'city' => $store->getConfig(self::XML_PATH_STORE_INFO_CITY),
            'postcode' => $store->getConfig(self::XML_PATH_STORE_INFO_POSTCODE),
            'region_id' => $store->getConfig(self::XML_PATH_STORE_INFO_REGION_CODE),
            'country_id' => $store->getConfig(self::XML_PATH_STORE_INFO_COUNTRY_CODE),
            'vat_number' => $store->getConfig(self::XML_PATH_STORE_INFO_VAT_NUMBER),
        ]);

        if ($info->getRegionId()) {
            $info->setRegion($this->regionFactory->create()->load($info->getRegionId())->getName());
        }

        if ($info->getCountryId()) {
            $info->setCountry($this->countryFactory->create()->loadByCode($info->getCountryId())->getName());
        }

        return $info;
    }

    /**
     * Retrieve formatted store address from config
     *
     * @param Store $store
     * @return string
     */
    public function getFormattedAddress(Store $store)
    {
        return $this->renderer->format($this->getStoreInformationObject($store));
    }
}
