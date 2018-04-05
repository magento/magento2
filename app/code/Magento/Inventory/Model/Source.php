<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
    public function getSourceCode()
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceCode($sourceCode)
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail($email)
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getContactName()
    {
        return $this->getData(self::CONTACT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setContactName($contactName)
    {
        $this->setData(self::CONTACT_NAME, $contactName);
    }

    /**
     * @inheritdoc
     */
    public function isEnabled()
    {
        return $this->getData(self::ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setEnabled($enabled)
    {
        $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getLatitude()
    {
        return $this->getData(self::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude($latitude)
    {
        $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @inheritdoc
     */
    public function getLongitude()
    {
        return $this->getData(self::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude($longitude)
    {
        $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @inheritdoc
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId($countryId)
    {
        $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @inheritdoc
     */
    public function getRegionId()
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId($regionId)
    {
        $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function getRegion()
    {
        return $this->getData(self::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion($region)
    {
        $this->setData(self::REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode()
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode($postcode)
    {
        $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @inheritdoc
     */
    public function setPhone($phone)
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @inheritdoc
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax($fax)
    {
        $this->setData(self::FAX, $fax);
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
        return $this->getData(self::CARRIER_LINKS);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierLinks($carrierLinks)
    {
        $this->setData(self::CARRIER_LINKS, $carrierLinks);
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
