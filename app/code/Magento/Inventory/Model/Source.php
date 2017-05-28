<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use \Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Class Source,
 * provides implementation of the SourceInterface which adds the possibilty
 * for a Merchant to map existing physical sources to some particular sales channels
 * this model holds the information like name and description of this physical sources
 */
class Source extends AbstractExtensibleModel implements SourceInterface
{
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
    public function setSourceId(int $sourceId): SourceInterface
    {
        $this->setData(SourceInterface::SOURCE_ID, $sourceId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->getData(SourceInterface::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): SourceInterface
    {
        $this->setData(SourceInterface::NAME, $name);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): string
    {
        return $this->getData(SourceInterface::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail(string $email): SourceInterface
    {
        $this->setData(SourceInterface::EMAIL, $email);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getContactName(): string
    {
        return $this->getData(SourceInterface::CONTACT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setContactName(string $contactName): SourceInterface
    {
        $this->setData(SourceInterface::CONTACT_NAME, $contactName);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): bool
    {
        return $this->getData(SourceInterface::IS_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(bool $active): SourceInterface
    {
        $this->setData(SourceInterface::IS_ACTIVE, $active);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): string
    {
        return $this->getData(SourceInterface::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription(string $description): SourceInterface
    {
        $this->setData(SourceInterface::DESCRIPTION, $description);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLatitude(): float
    {
        return $this->getData(SourceInterface::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude(float $latitude): SourceInterface
    {
        $this->setData(SourceInterface::LATITUDE, $latitude);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLongitude(): float
    {
        return $this->getData(SourceInterface::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude(float $longitude): SourceInterface
    {
        $this->setData(SourceInterface::LONGITUDE, $longitude);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountryId(): string
    {
        return $this->getData(SourceInterface::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId(string $countryId): SourceInterface
    {
        $this->setData(SourceInterface::COUNTRY_ID, $countryId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegionId(): int
    {
        return $this->getData(SourceInterface::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId(int $regionId): SourceInterface
    {
        $this->setData(SourceInterface::REGION_ID, $regionId);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRegion(): string
    {
        return $this->getData(SourceInterface::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion(string $region): SourceInterface
    {
        $this->setData(SourceInterface::REGION, $region);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity(): string
    {
        return $this->getData(SourceInterface::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity(string $city): SourceInterface
    {
        $this->setData(SourceInterface::CITY, $city);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStreet(): string
    {
        return $this->getData(SourceInterface::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet(string $street): SourceInterface
    {
        $this->setData(SourceInterface::STREET, $street);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPostcode(): string
    {
        return $this->getData(SourceInterface::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode(string $postcode): SourceInterface
    {
        $this->setData(SourceInterface::POSTCODE, $postcode);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPhone(): string
    {
        return $this->getData(SourceInterface::PHONE);
    }

    /**
     * @inheritdoc
     */
    public function setPhone(string $phone): SourceInterface
    {
        $this->setData(SourceInterface::PHONE, $phone);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getFax(): string
    {
        return $this->getData(SourceInterface::FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax(string $fax): SourceInterface
    {
        $this->setData(SourceInterface::FAX, $fax);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): int
    {
        return $this->getData(SourceInterface::PRIORITY);
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority): SourceInterface
    {
        $this->setData(SourceInterface::PRIORITY, $priority);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCarrierLinks(): array
    {
        return $this->getData(SourceInterface::CARRIER_LINKS);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierLinks(array $carrierLinks): SourceInterface
    {
        $this->setData(SourceInterface::CARRIER_LINKS, $carrierLinks);
        return $this;
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
