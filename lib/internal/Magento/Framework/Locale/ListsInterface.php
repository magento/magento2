<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

interface ListsInterface
{
    /**
     * Get options array for locale dropdown in current locale
     *
     * @return array
     */
    public function getOptionLocales();

    /**
     * Get translated to original locale options array for locale dropdown
     *
     * @return array
     */
    public function getTranslatedOptionLocales();

    /**
     * Retrieve timezone option list
     *
     * @return array
     */
    public function getOptionTimezones();

    /**
     * Retrieve days of week option list
     *
     * @param bool $preserveCodes
     * @param bool $ucFirstCode
     *
     * @return array
     */
    public function getOptionWeekdays($preserveCodes = false, $ucFirstCode = false);

    /**
     * Retrieve country option list
     *
     * @return array
     */
    public function getOptionCountries();

    /**
     * Retrieve currency option list
     *
     * @return array
     */
    public function getOptionCurrencies();

    /**
     * Retrieve all currency option list
     *
     * @return array
     */
    public function getOptionAllCurrencies();

    /**
     * Returns the localized country name
     *
     * @param  string $value  Name to get detailed information about
     * @param  string $locale Optional locale string
     * @return string
     */
    public function getCountryTranslation($value, $locale = null);
}
