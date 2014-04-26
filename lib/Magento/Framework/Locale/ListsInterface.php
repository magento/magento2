<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * Returns localized informations as array, supported are several
     * types of information.
     * For detailed information about the types look into the documentation
     *
     * @param  string $path (Optional) Type of information to return
     * @param  string $value (Optional) Value for detail list
     * @return array Array with the wished information in the given language
     */
    public function getTranslationList($path = null, $value = null);

    /**
     * Returns the localized country name
     *
     * @param  $value string Name to get detailed information about
     * @return array
     */
    public function getCountryTranslation($value);

    /**
     * Returns an array with the name of all countries translated to the given language
     *
     * @return array
     */
    public function getCountryTranslationList();
}
