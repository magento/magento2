<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * @api
 * @since 2.0.0
 */
interface ListsInterface extends OptionInterface
{
    /**
     * Retrieve timezone option list
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionTimezones();

    /**
     * Retrieve days of week option list
     *
     * @param bool $preserveCodes
     * @param bool $ucFirstCode
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionWeekdays($preserveCodes = false, $ucFirstCode = false);

    /**
     * Retrieve country option list
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionCountries();

    /**
     * Retrieve currency option list
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionCurrencies();

    /**
     * Retrieve all currency option list
     *
     * @return array
     * @since 2.0.0
     */
    public function getOptionAllCurrencies();

    /**
     * Returns the localized country name
     *
     * @param  string $value  Name to get detailed information about
     * @param  string $locale Optional locale string
     * @return string
     * @since 2.0.0
     */
    public function getCountryTranslation($value, $locale = null);
}
