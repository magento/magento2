<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Inventory\Model\ResourceModel\Source as SourceResourceModel;
use Magento\InventoryApi\Api\Data\SourceExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * {@inheritdoc}
 *
 * @codeCoverageIgnore
 */
class Source extends AbstractExtensibleModel implements SourceInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceResourceModel::class);
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
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->getData(SourceInterface::ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        $this->setData(SourceInterface::ENABLED, $enabled);
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
    }

    /**
     * @inheritdoc
     */
    public function isUseDefaultCarrierConfig()
    {
        return $this->getData(self::USE_DEFAULT_CARRIER_CONFIG);
    }

    /**
     * @inheritdoc
     */
    public function setUseDefaultCarrierConfig($useDefaultCarrierConfig)
    {
        $this->setData(self::USE_DEFAULT_CARRIER_CONFIG, $useDefaultCarrierConfig);
    }

    /**
     * @inheritdoc
     */
    public function getCarrierLinks()
    {
        return $this->getData(SourceInterface::CARRIER_LINKS);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierLinks($carrierLinks)
    {
        $this->setData(SourceInterface::CARRIER_LINKS, $carrierLinks);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SourceExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
