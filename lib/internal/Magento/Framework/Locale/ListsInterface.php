<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * @api
 * @since 100.0.2
 */
interface ListsInterface extends OptionInterface
{
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
