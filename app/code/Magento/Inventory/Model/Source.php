<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractModel;
use \Magento\InventoryApi\Api\Data\SourceInterface;

class Source extends AbstractModel implements SourceInterface
{

    /**
     * Name of the resource collection model
     *
     * @codingStandardsIgnore
     * @var string
     */
    protected $_collectionName = 'Magento\Inventory\Model\Resource\Source\Collection';

    /**
     * Initialize resource model
     *
     * @codingStandardsIgnore
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Inventory\Model\Resource\Source');
    }

    /**
     * Get source id.
     *
     * @return int|null
     */
    public function getSourceId()
    {
        return $this->getData(SourceInterface::SOURCE_ID);
    }

    /**
     * Set source id.
     *
     * @param int $sourceId
     *
     * @return $this
     */
    public function setSourceId($sourceId)
    {
        $this->setData(SourceInterface::SOURCE_ID, $sourceId);
        return $this;
    }

    /**
     * Get source name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(SourceInterface::NAME);
    }

    /**
     * Set source name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setData(SourceInterface::NAME, $name);
        return $this;
    }

    /**
     * Get source email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(SourceInterface::EMAIL);
    }

    /**
     * Set source email
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->setData(SourceInterface::EMAIL, $email);
        return $this;
    }

    /**
     * Get source contact name.
     *
     * @return string
     */
    public function getContactName()
    {
        return $this->getData(SourceInterface::CONTACT_NAME);
    }

    /**
     * Set source contact name.
     *
     * @param string $contactName
     *
     * @return $this
     */
    public function setContactName($contactName)
    {
        $this->setData(SourceInterface::CONTACT_NAME, $contactName);
        return $this;
    }

    /**
     * Check if source is enabled.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->getData(SourceInterface::IS_ACTIVE);
    }

    /**
     * Enable or disable source.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setIsActive($active)
    {
        $this->setData(SourceInterface::IS_ACTIVE, $active);
        return $this;
    }

    /**
     * Get source description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(SourceInterface::DESCRIPTION);
    }

    /**
     * Set source description.
     *
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->setData(SourceInterface::DESCRIPTION, $description);
        return $this;
    }

    /**
     * Get source latitude.
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->getData(SourceInterface::LATITUDE);
    }

    /**
     * Set source latitude.
     *
     * @param float $latitude
     *
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->setData(SourceInterface::LATITUDE, $latitude);
        return $this;
    }

    /**
     * Get source longitude.
     *
     * @return int
     */
    public function getLongitude()
    {
        return $this->getData(SourceInterface::LONGITUDE);
    }

    /**
     * Set source longitude.
     *
     * @param int $longitude
     *
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->setData(SourceInterface::LONGITUDE, $longitude);
        return $this;
    }

    /**
     * Get source country id.
     *
     * @return string
     */
    public function getCountryId()
    {
        return $this->getData(SourceInterface::COUNTRY_ID);
    }

    /**
     * Set source country id.
     *
     * @param string $countryId
     *
     * @return $this
     */
    public function setCountryId($countryId)
    {
        $this->setData(SourceInterface::COUNTRY_ID, $countryId);
        return $this;
    }

    /**
     * Get region id if source has registered region.
     *
     * @return int
     */
    public function getRegionId()
    {
        return $this->getData(SourceInterface::REGION_ID);
    }

    /**
     * Set region id if source has registered region.
     *
     * @param int $regionId
     *
     * @return $this
     */
    public function setRegionId($regionId)
    {
        $this->setData(SourceInterface::REGION_ID, $regionId);
        return $this;
    }

    /**
     * Get region title if source has custom region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(SourceInterface::REGION);
    }

    /**
     * Set source region title.
     *
     * @param string $region
     *
     * @return $this
     */
    public function setRegion($region)
    {
        $this->setData(SourceInterface::REGION, $region);
        return $this;
    }

    /**
     * Get source city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(SourceInterface::CITY);
    }

    /**
     * Set source city.
     *
     * @param string $city
     *
     * @return $this
     */
    public function setCity($city)
    {
        $this->setData(SourceInterface::CITY, $city);
        return $this;
    }

    /**
     * Get source street name.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->getData(SourceInterface::STREET);
    }

    /**
     * Set source street name.
     *
     * @param string $street
     *
     * @return $this
     */
    public function setStreet($street)
    {
        $this->setData(SourceInterface::STREET, $street);
        return $this;
    }

    /**
     * Get source post code.
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(SourceInterface::POSTCODE);
    }

    /**
     * Set source post code.
     *
     * @param string $postcode
     *
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->setData(SourceInterface::POSTCODE, $postcode);
        return $this;
    }

    /**
     * Get source phone number.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData(SourceInterface::PHONE);
    }

    /**
     * Set source phone number.
     *
     * @param string $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->setData(SourceInterface::PHONE, $phone);
        return $this;
    }

    /**
     * Get source fax.
     *
     * @return string
     */
    public function getFax()
    {
        return $this->getData(SourceInterface::FAX);
    }

    /**
     * Set source fax.
     *
     * @param string $fax
     *
     * @return $this
     */
    public function setFax($fax)
    {
        $this->setData(SourceInterface::FAX, $fax);
        return $this;
    }

    /**
     * Get source priority
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->getData(SourceInterface::PRIORITY);
    }

    /**
     * Set source priority
     *
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->setData(SourceInterface::PRIORITY, $priority);
        return $this;
    }

    /**
     * @return \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[]
     */
    public function getCarrierLinks()
    {
        return $this->getData(SourceInterface::PRIORITY);
    }

    /**
     * @param \Magento\InventoryApi\Api\Data\SourceCarrierLinkInterface[] $carrierLinks
     *
     * @return $this
     */
    public function setCarrierLinks($carrierLinks)
    {
        $this->setData(SourceInterface::CARRIER_LINKS, $carrierLinks);
        return $this;
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\InventoryApi\Api\Data\SourceExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getData(SourceInterface::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
     *
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
    ) {
        $this->setData(SourceInterface::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
        return $this;
    }
}