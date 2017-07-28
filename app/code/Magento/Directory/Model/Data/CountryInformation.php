<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Country Information
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class CountryInformation extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Directory\Api\Data\CountryInformationInterface
{
    const KEY_COUNTRY_ID = 'country_id';
    const KEY_COUNTRY_TWO_LETTER_ABBREVIATION = 'country_abbreviation2';
    const KEY_COUNTRY_THREE_LETTER_ABBREVIATION = 'country_abbreviation3';
    const KEY_COUNTRY_FULL_NAME_LOCALE = 'country_full_name_locale';
    const KEY_COUNTRY_FULL_NAME_ENGLISH = 'country_full_name_english';
    const KEY_COUNTRY_AVAILABLE_REGIONS = 'country_available_regions';

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getId()
    {
        return $this->_get(self::KEY_COUNTRY_ID);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $id);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getTwoLetterAbbreviation()
    {
        return $this->_get(self::KEY_COUNTRY_TWO_LETTER_ABBREVIATION);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setTwoLetterAbbreviation($abbreviation)
    {
        return $this->setData(self::KEY_COUNTRY_TWO_LETTER_ABBREVIATION, $abbreviation);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getThreeLetterAbbreviation()
    {
        return $this->_get(self::KEY_COUNTRY_THREE_LETTER_ABBREVIATION);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setThreeLetterAbbreviation($abbreviation)
    {
        return $this->setData(self::KEY_COUNTRY_THREE_LETTER_ABBREVIATION, $abbreviation);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getFullNameLocale()
    {
        return $this->_get(self::KEY_COUNTRY_FULL_NAME_LOCALE);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setFullNameLocale($fullNameLocale)
    {
        return $this->setData(self::KEY_COUNTRY_FULL_NAME_LOCALE, $fullNameLocale);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getFullNameEnglish()
    {
        return $this->_get(self::KEY_COUNTRY_FULL_NAME_ENGLISH);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setFullNameEnglish($fullNameEnglish)
    {
        return $this->setData(self::KEY_COUNTRY_FULL_NAME_ENGLISH, $fullNameEnglish);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getAvailableRegions()
    {
        return $this->_get(self::KEY_COUNTRY_AVAILABLE_REGIONS);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setAvailableRegions($availableRegions)
    {
        return $this->setData(self::KEY_COUNTRY_AVAILABLE_REGIONS, $availableRegions);
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
