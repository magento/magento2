<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents physical storage, i.e. brick and mortar store or warehouse
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SOURCE_CODE = 'source_code';
    const NAME = 'name';
    const CONTACT_NAME = 'contact_name';
    const EMAIL = 'email';
    const ENABLED = 'enabled';
    const DESCRIPTION = 'description';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const REGION = 'region';
    const CITY = 'city';
    const STREET = 'street';
    const POSTCODE = 'postcode';
    const PHONE = 'phone';
    const FAX = 'fax';
    const USE_DEFAULT_CARRIER_CONFIG = 'use_default_carrier_config';
    const CARRIER_LINKS = 'carrier_links';

    /**
     * Get source code
     *
     * @return string|null
     */
    public function getSourceCode();

    /**
     * Set source code
     *
     * @param string|null $sourceCode
     * @return void
     */
    public function setSourceCode($sourceCode);

    /**
     * Get source name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set source name
     *
     * @param string|null $name
     * @return void
     */
    public function setName($name);

    /**
     * Get source email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set source email
     *
     * @param string|null $email
     * @return void
     */
    public function setEmail($email);

    /**
     * Get source contact name
     *
     * @return string|null
     */
    public function getContactName();

    /**
     * Set source contact name
     *
     * @param string|null $contactName
     * @return void
     */
    public function setContactName($contactName);

    /**
     * Check if source is enabled. For new entity can be null
     *
     * @return bool|null
     */
    public function isEnabled();

    /**
     * Enable or disable source
     *
     * @param bool|null $enabled
     * @return void
     */
    public function setEnabled($enabled);

    /**
     * Get source description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set source description
     *
     * @param string|null $description
     * @return void
     */
    public function setDescription($description);

    /**
     * Get source latitude
     *
     * @return float|null
     */
    public function getLatitude();

    /**
     * Set source latitude
     *
     * @param float|null $latitude
     * @return void
     */
    public function setLatitude($latitude);

    /**
     * Get source longitude
     *
     * @return float|null
     */
    public function getLongitude();

    /**
     * Set source longitude
     *
     * @param float|null $longitude
     * @return void
     */
    public function setLongitude($longitude);

    /**
     * Get source country id
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set source country id
     *
     * @param string|null $countryId
     * @return void
     */
    public function setCountryId($countryId);

    /**
     * Get region id if source has registered region.
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set region id if source has registered region.
     *
     * @param int|null $regionId
     * @return void
     */
    public function setRegionId($regionId);

    /**
     * Get region title if source has custom region
     *
     * @return string|null
     */
    public function getRegion();

    /**
     * Set source region title
     *
     * @param string|null $region
     * @return void
     */
    public function setRegion($region);

    /**
     * Get source city
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set source city
     *
     * @param string|null $city
     * @return void
     */
    public function setCity($city);

    /**
     * Get source street name
     *
     * @return string|null
     */
    public function getStreet();

    /**
     * Set source street name
     *
     * @param string|null $street
     * @return void
     */
    public function setStreet($street);

    /**
     * Get source post code
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set source post code
     *
     * @param string|null $postcode
     * @return void
     */
    public function setPostcode($postcode);

    /**
     * Get source phone number
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set source phone number
     *
     * @param string|null $phone
     * @return void
     */
    public function setPhone($phone);

    /**
     * Get source fax
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set source fax
     *
     * @param string|null $fax
     * @return void
     */
    public function setFax($fax);

    /**
     * Check is need to use default config
     *
     * @return bool|null
     */
    public function isUseDefaultCarrierConfig();

    /**
     * @param bool|null $useDefaultCarrierConfig
     * @return void
     */
    public function setUseDefaultCarrierConfig($useDefaultCarrierConfig);

    /**
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[]|null
     */
    public function getCarrierLinks();

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[]|null $carrierLinks
     * @return void
     */
    public function setCarrierLinks($carrierLinks);

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryApi\Api\Data\SourceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SourceExtensionInterface $extensionAttributes);
}
