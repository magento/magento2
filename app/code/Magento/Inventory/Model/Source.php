<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractModel;
use \Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class Source
 * @package Magento\Inventory\Model
 */
class Source extends AbstractModel implements SourceInterface
{
    /**
     * @inheritdoc
     */
    protected $_collectionName = \Magento\Inventory\Model\Resource\Source\Collection::class;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Magento\Inventory\Model\Resource\Source::class);
    }

    /**
     * @inheritdoc
     */
    public function getSourceId()
    {
        return $this->getData(SourceInterface::SOURCE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSourceId($sourceId)
    {
        $this->setData(SourceInterface::SOURCE_ID, $sourceId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(SourceInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(SourceInterface::NAME, $name);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->getData(SourceInterface::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->setData(SourceInterface::EMAIL, $email);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContactName()
    {
        return $this->getData(SourceInterface::CONTACT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setContactName($contactName)
    {
        $this->setData(SourceInterface::CONTACT_NAME, $contactName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive()
    {
        return $this->getData(SourceInterface::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive($active)
    {
        $this->setData(SourceInterface::IS_ACTIVE, $active);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(SourceInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(SourceInterface::DESCRIPTION, $description);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLatitude()
    {
        return $this->getData(SourceInterface::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude($latitude)
    {
        $this->setData(SourceInterface::LATITUDE, $latitude);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLongitude()
    {
        return $this->getData(SourceInterface::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude($longitude)
    {
        $this->setData(SourceInterface::LONGITUDE, $longitude);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->getData(SourceInterface::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($countryId)
    {
        $this->setData(SourceInterface::COUNTRY_ID, $countryId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegionId()
    {
        return $this->getData(SourceInterface::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        $this->setData(SourceInterface::REGION_ID, $regionId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegion()
    {
        return $this->getData(SourceInterface::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        $this->setData(SourceInterface::REGION, $region);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(SourceInterface::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->setData(SourceInterface::CITY, $city);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        return $this->getData(SourceInterface::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        $this->setData(SourceInterface::STREET, $street);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->getData(SourceInterface::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        $this->setData(SourceInterface::POSTCODE, $postcode);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPhone()
    {
        return $this->getData(SourceInterface::PHONE);
    }

    /**
     * @inheritdoc
     */
    public function setPhone($phone)
    {
        $this->setData(SourceInterface::PHONE, $phone);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFax()
    {
        return $this->getData(SourceInterface::FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax($fax)
    {
        $this->setData(SourceInterface::FAX, $fax);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->getData(SourceInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->setData(SourceInterface::PRIORITY, $priority);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCarrierLinks()
    {
        // TODO: Implement getCarrierLinks() method.
    }

    /**
     * @inheritdoc
     */
    public function setCarrierLinks($carrierLinks)
    {
        // TODO: Implement setCarrierLinks() method.
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\SourceExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}