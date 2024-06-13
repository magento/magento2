<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Directory\Api;

/**
 * Country information acquirer interface
 *
 * @api
 * @since 100.0.2
 */
interface CountryInformationAcquirerInterface
{
    /**
     * Get all countries and regions information for the store.
     *
     * @return \Magento\Directory\Api\Data\CountryInformationInterface[]
     */
    public function getCountriesInfo();

    /**
     * Get country and region information for the store.
     *
     * @param string $countryId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\Directory\Api\Data\CountryInformationInterface
     */
    public function getCountryInfo($countryId);
}
