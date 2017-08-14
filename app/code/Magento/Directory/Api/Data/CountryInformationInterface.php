<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Api\Data;

/**
 * Country Information interface.
 *
 * @api
 * @since 100.0.2
 */
interface CountryInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get the country id for the store.
     *
     * @return string
     */
    public function getId();

    /**
     * Set the country id for the store.
     *
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get the country 2 letter abbreviation for the store.
     *
     * @return string
     */
    public function getTwoLetterAbbreviation();

    /**
     * Set the country 2 letter abbreviation for the store.
     *
     * @param string $abbreviation
     * @return $this
     */
    public function setTwoLetterAbbreviation($abbreviation);

    /**
     * Get the country 3 letter abbreviation for the store.
     *
     * @return string
     */
    public function getThreeLetterAbbreviation();

    /**
     * Set the country 3 letter abbreviation for the store.
     *
     * @param string $abbreviation
     * @return $this
     */
    public function setThreeLetterAbbreviation($abbreviation);

    /**
     * Get the country full name (in store locale) for the store.
     *
     * @return string
     */
    public function getFullNameLocale();

    /**
     * Set the country full name (in store locale) for the store.
     *
     * @param string $fullNameLocale
     * @return $this
     */
    public function setFullNameLocale($fullNameLocale);

    /**
     * Get the country full name (in English) for the store.
     *
     * @return string
     */
    public function getFullNameEnglish();

    /**
     * Set the country full name (in English) for the store.
     *
     * @param string $fullNameEnglish
     * @return $this
     */
    public function setFullNameEnglish($fullNameEnglish);

    /**
     * Get the available regions for the store.
     *
     * @return \Magento\Directory\Api\Data\RegionInformationInterface[]|null
     */
    public function getAvailableRegions();

    /**
     * Set the available regions for the store
     *
     * @param \Magento\Directory\Api\Data\RegionInformationInterface[] $availableRegions
     * @return $this
     */
    public function setAvailableRegions($availableRegions);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Directory\Api\Data\CountryInformationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
    );
}
