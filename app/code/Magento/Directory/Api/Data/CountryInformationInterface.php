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
 * @since 2.0.0
 */
interface CountryInformationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get the country id for the store.
     *
     * @return string
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set the country id for the store.
     *
     * @param string $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get the country 2 letter abbreviation for the store.
     *
     * @return string
     * @since 2.0.0
     */
    public function getTwoLetterAbbreviation();

    /**
     * Set the country 2 letter abbreviation for the store.
     *
     * @param string $abbreviation
     * @return $this
     * @since 2.0.0
     */
    public function setTwoLetterAbbreviation($abbreviation);

    /**
     * Get the country 3 letter abbreviation for the store.
     *
     * @return string
     * @since 2.0.0
     */
    public function getThreeLetterAbbreviation();

    /**
     * Set the country 3 letter abbreviation for the store.
     *
     * @param string $abbreviation
     * @return $this
     * @since 2.0.0
     */
    public function setThreeLetterAbbreviation($abbreviation);

    /**
     * Get the country full name (in store locale) for the store.
     *
     * @return string
     * @since 2.0.0
     */
    public function getFullNameLocale();

    /**
     * Set the country full name (in store locale) for the store.
     *
     * @param string $fullNameLocale
     * @return $this
     * @since 2.0.0
     */
    public function setFullNameLocale($fullNameLocale);

    /**
     * Get the country full name (in English) for the store.
     *
     * @return string
     * @since 2.0.0
     */
    public function getFullNameEnglish();

    /**
     * Set the country full name (in English) for the store.
     *
     * @param string $fullNameEnglish
     * @return $this
     * @since 2.0.0
     */
    public function setFullNameEnglish($fullNameEnglish);

    /**
     * Get the available regions for the store.
     *
     * @return \Magento\Directory\Api\Data\RegionInformationInterface[]|null
     * @since 2.0.0
     */
    public function getAvailableRegions();

    /**
     * Set the available regions for the store
     *
     * @param \Magento\Directory\Api\Data\RegionInformationInterface[] $availableRegions
     * @return $this
     * @since 2.0.0
     */
    public function setAvailableRegions($availableRegions);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Directory\Api\Data\CountryInformationExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Directory\Api\Data\CountryInformationExtensionInterface $extensionAttributes
    );
}
