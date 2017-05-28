<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface SourceInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SOURCE_ID = 'source_id';
    const NAME = 'name';
    const CONTACT_NAME = 'contact_name';
    const EMAIL = 'email';
    const IS_ACTIVE = 'is_active';
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
    const PRIORITY = 'priority';
    const CARRIER_LINKS = 'carrier_links';
    /**#@-*/

    /**
     * Get source id.
     *
     * @return int|null
     */
    public function getSourceId();

    /**
     * Set source id.
     *
     * @param int $sourceId
     * @return SourceInterface
     */
    public function setSourceId(int $sourceId): SourceInterface;

    /**
     * Get source name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set source name.
     *
     * @param string $name
     * @return SourceInterface
     */
    public function setName(string $name): SourceInterface;

    /**
     * Get source email
     *
     * @return string
     */
    public function getEmail(): string;

    /**
     * Set source email
     *
     * @param string $email
     * @return SourceInterface
     */
    public function setEmail(string $email): SourceInterface;

    /**
     * Get source contact name.
     *
     * @return string
     */
    public function getContactName(): string;

    /**
     * Set source contact name.
     *
     * @param string $contactName
     * @return SourceInterface
     */
    public function setContactName(string $contactName): SourceInterface;

    /**
     * Check if source is enabled.
     *
     * @return bool
     */
    public function getIsActive(): bool;

    /**
     * Enable or disable source.
     *
     * @param bool $active
     * @return SourceInterface
     */
    public function setIsActive(bool $active): SourceInterface;

    /**
     * Get source description.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Set source description.
     *
     * @param string $description
     * @return SourceInterface
     */
    public function setDescription(string $description): SourceInterface;

    /**
     * Get source latitude.
     *
     * @return float
     */
    public function getLatitude(): float;

    /**
     * Set source latitude.
     *
     * @param float $latitude
     * @return SourceInterface
     */
    public function setLatitude(float $latitude): SourceInterface;

    /**
     * Get source longitude.
     *
     * @return float
     */
    public function getLongitude(): float;

    /**
     * Set source longitude.
     *
     * @param float $longitude
     * @return SourceInterface
     */
    public function setLongitude(float $longitude): SourceInterface;

    /**
     * Get source country id.
     *
     * @return string
     */
    public function getCountryId(): string;

    /**
     * Set source country id.
     *
     * @param string $countryId
     * @return SourceInterface
     */
    public function setCountryId(string $countryId): SourceInterface;

    /**
     * Get region id if source has registered region.
     *
     * @return int
     */
    public function getRegionId(): int;

    /**
     * Set region id if source has registered region.
     *
     * @param int $regionId
     * @return SourceInterface
     */
    public function setRegionId(int $regionId): SourceInterface;

    /**
     * Get region title if source has custom region
     *
     * @return string
     */
    public function getRegion(): string;

    /**
     * Set source region title.
     *
     * @param string $region
     * @return SourceInterface
     */
    public function setRegion(string $region): SourceInterface;

    /**
     * Get source city.
     *
     * @return string
     */
    public function getCity(): string;

    /**
     * Set source city.
     *
     * @param string $city
     * @return SourceInterface
     */
    public function setCity(string $city): SourceInterface;

    /**
     * Get source street name.
     *
     * @return string
     */
    public function getStreet(): string;

    /**
     * Set source street name.
     *
     * @param string $street
     * @return SourceInterface
     */
    public function setStreet(string $street): SourceInterface;

    /**
     * Get source post code.
     *
     * @return string
     */
    public function getPostcode(): string;

    /**
     * Set source post code.
     *
     * @param string $postcode
     * @return SourceInterface
     */
    public function setPostcode(string $postcode): SourceInterface;

    /**
     * Get source phone number.
     *
     * @return string
     */
    public function getPhone(): string;

    /**
     * Set source phone number.
     *
     * @param string $phone
     * @return SourceInterface
     */
    public function setPhone(string $phone): SourceInterface;

    /**
     * Get source fax.
     *
     * @return string
     */
    public function getFax(): string;

    /**
     * Set source fax.
     *
     * @param string $fax
     * @return SourceInterface
     */
    public function setFax(string $fax): SourceInterface;

    /**
     * Get source priority
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Set source priority
     *
     * @param int $priority
     * @return SourceInterface
     */
    public function setPriority(int $priority): SourceInterface;

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[] $carrierLinks
     * @return SourceInterface
     */
    public function setCarrierLinks(array $carrierLinks): SourceInterface;

    /**
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[]
     */
    public function getCarrierLinks(): array;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\InventoryApi\Api\Data\SourceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
    );
}
