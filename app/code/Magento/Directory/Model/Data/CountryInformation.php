<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Data;

/**
 * Class Country Information
 *
 * @codeCoverageIgnore
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
     */
    public function getId()
    {
        return $this->_get(self::KEY_COUNTRY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($id)
    {
        return $this->setData(self::KEY_COUNTRY_ID, $id);
    }

    /**
     * @inheritDoc
     */
    public function getTwoLetterAbbreviation()
    {
        return $this->_get(self::KEY_COUNTRY_TWO_LETTER_ABBREVIATION);
    }

    /**
     * @inheritDoc
     */
    public function setTwoLetterAbbreviation($abbreviation)
    {
        return $this->setData(self::KEY_COUNTRY_TWO_LETTER_ABBREVIATION, $abbreviation);
    }

    /**
     * @inheritDoc
     */
    public function getThreeLetterAbbreviation()
    {
        return $this->_get(self::KEY_COUNTRY_THREE_LETTER_ABBREVIATION);
    }

    /**
     * @inheritDoc
     */
    public function setThreeLetterAbbreviation($abbreviation)
    {
        return $this->setData(self::KEY_COUNTRY_THREE_LETTER_ABBREVIATION, $abbreviation);
    }

    /**
     * @inheritDoc
     */
    public function getFullNameLocale()
    {
        return $this->_get(self::KEY_COUNTRY_FULL_NAME_LOCALE);
    }

    /**
     * @inheritDoc
     */
    public function setFullNameLocale($fullNameLocale)
    {
        return $this->setData(self::KEY_COUNTRY_FULL_NAME_LOCALE, $fullNameLocale);
    }

    /**
     * @inheritDoc
     */
    public function getFullNameEnglish()
    {
        return $this->_get(self::KEY_COUNTRY_FULL_NAME_ENGLISH);
    }

    /**
     * @inheritDoc
     */
    public function setFullNameEnglish($fullNameEnglish)
    {
        return $this->setData(self::KEY_COUNTRY_FULL_NAME_ENGLISH, $fullNameEnglish);
    }

    /**
     * @inheritDoc
     */
    public function getAvailableRegions()
    {
        return $this->_get(self::KEY_COUNTRY_AVAILABLE_REGIONS);
    }

    /**
     * @inheritDoc
     */
    public function setAvailableRegions($availableRegions)
    {
        return $this->setData(self::KEY_COUNTRY_AVAILABLE_REGIONS, $availableRegions);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
