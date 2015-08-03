<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\Object;
use Magento\Store\Model\Store;
use Magento\Store\Model\Address\Renderer;

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
    protected $renderer = null;

    /**
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Retrieve generic object with all the misc store information values
     *
     * @param \Magento\Store\Model\Store $store
     * @return Object
     */
    public function getStoreInformation(Store $store)
    {
        $information = new Object([
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

        if ($information->getRegionId()) {

        }

        if ($information->getCountryId()) {

        }

        return $information;
    }

    /**
     * Retrieve formatted store address from config
     *
     * @param \Magento\Store\Model\Store $store
     * @return string
     */
    public function getFormattedAddress(Store $store)
    {
        return $this->renderer->format($this->getStoreInformation($store), 'html');
    }
}
