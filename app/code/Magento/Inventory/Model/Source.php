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
    public function getSourceCode(): ?string
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceCode(?string $sourceCode): void
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(?string $name): void
    {
        $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getEmail(): ?string
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritdoc
     */
    public function setEmail(?string $email): void
    {
        $this->setData(self::EMAIL, $email);
    }

    /**
     * @inheritdoc
     */
    public function getContactName(): ?string
    {
        return $this->getData(self::CONTACT_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setContactName(?string $contactName): void
    {
        $this->setData(self::CONTACT_NAME, $contactName);
    }

    /**
     * @inheritdoc
     */
    public function isEnabled() :?bool
    {
        return $this->getData(self::ENABLED) === null ?
            null:
            (bool)$this->getData(self::ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setEnabled(?bool $enabled): void
    {
        $this->setData(self::ENABLED, $enabled);
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setDescription(?string $description): void
    {
        $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getLatitude(): ?float
    {
        return $this->getData(self::LATITUDE) === null ?
            null:
            (float)$this->getData(self::LATITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLatitude(?float $latitude): void
    {
        $this->setData(self::LATITUDE, $latitude);
    }

    /**
     * @inheritdoc
     */
    public function getLongitude(): ?float
    {
        return $this->getData(self::LONGITUDE) === null ?
            null:
            (float)$this->getData(self::LONGITUDE);
    }

    /**
     * @inheritdoc
     */
    public function setLongitude(?float $longitude): void
    {
        $this->setData(self::LONGITUDE, $longitude);
    }

    /**
     * @inheritdoc
     */
    public function getCountryId(): ?string
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCountryId(?string $countryId): void
    {
        $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * @inheritdoc
     */
    public function getRegionId(): ?int
    {
        return $this->getData(self::REGION_ID) === null ?
            null:
            (int)$this->getData(self::REGION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setRegionId(?int $regionId): void
    {
        $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritdoc
     */
    public function getRegion(): ?string
    {
        return $this->getData(self::REGION);
    }

    /**
     * @inheritdoc
     */
    public function setRegion(?string $region): void
    {
        $this->setData(self::REGION, $region);
    }

    /**
     * @inheritdoc
     */
    public function getCity(): ?string
    {
        return $this->getData(self::CITY);
    }

    /**
     * @inheritdoc
     */
    public function setCity(?string $city): void
    {
        $this->setData(self::CITY, $city);
    }

    /**
     * @inheritdoc
     */
    public function getStreet(): ?string
    {
        return $this->getData(self::STREET);
    }

    /**
     * @inheritdoc
     */
    public function setStreet(?string $street): void
    {
        $this->setData(self::STREET, $street);
    }

    /**
     * @inheritdoc
     */
    public function getPostcode(): ?string
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritdoc
     */
    public function setPostcode(?string $postcode): void
    {
        $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @inheritdoc
     */
    public function getPhone(): ?string
    {
        return $this->getData(self::PHONE);
    }

    /**
     * @inheritdoc
     */
    public function setPhone(?string $phone): void
    {
        $this->setData(self::PHONE, $phone);
    }

    /**
     * @inheritdoc
     */
    public function getFax(): ?string
    {
        return $this->getData(self::FAX);
    }

    /**
     * @inheritdoc
     */
    public function setFax(?string $fax): void
    {
        $this->setData(self::FAX, $fax);
    }

    /**
     * @inheritdoc
     */
    public function isUseDefaultCarrierConfig(): ?bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setUseDefaultCarrierConfig(?bool $useDefaultCarrierConfig): void
    {
        $this->setData(self::USE_DEFAULT_CARRIER_CONFIG, $useDefaultCarrierConfig);
    }

    /**
     * @inheritdoc
     */
    public function getCarrierLinks(): ?array
    {
        return $this->getData(self::CARRIER_LINKS);
    }

    /**
     * @inheritdoc
     */
    public function setCarrierLinks(?array $carrierLinks): void
    {
        $this->setData(self::CARRIER_LINKS, $carrierLinks);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes(): ?SourceExtensionInterface
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
    public function setExtensionAttributes(SourceExtensionInterface $extensionAttributes): void
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
