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

namespace Magento\Locale;

class Lists
{
    /**
     * @var Data\Country
     */
    protected $country;

    /**
     * @var Data\Currency
     */
    protected $currency;

    /**
     * @var Data\Language
     */
    protected $language;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var Data\Timezone
     */
    protected $timezone;

    /**
     * @param Data\Country $country
     * @param Data\Currency $currency
     * @param Data\Language $language
     * @param Data\Timezone $timezone
     * @param Data\Locale $locale
     */
    public function __construct(
        Data\Country $country,
        Data\Currency $currency,
        Data\Language $language,
        Data\Timezone $timezone,
        Data\Locale $locale
    ) {
        $this->country = $country;
        $this->currency = $currency;
        $this->language = $language;
        $this->timezone = $timezone;
        $this->locale = $locale;
    }

    /**
     * Retrieve list of timezones
     *
     * @return array
     */
    public function getTimezoneList()
    {
        $list = [];
        foreach ($this->timezone->getData() as $code => $value) {
            $list[$code] = $value . ' (' . $code . ')';
        }
        asort($list);
        return $list;
    }

    /**
     * Retrieve list of currencies
     *
     * @return array
     */
    public function getCurrencyList()
    {
        $list = $this->currency->getData();
        foreach ($this->currency->getData() as $code => $value) {
            $list[$code] = $value . ' (' . $code . ')';
        }
        asort($list);
        return $list;
    }

    /**
     * Retrieve list of locales
     *
     * @return  array
     */
    public function getLocaleList()
    {
        $languages = $this->language->getData();
        $countries = $this->country->getData();

        $list = [];
        foreach ($this->locale->getData() as $code) {
            if (strstr($code, '_')) {
                $data = explode('_', $code);
                if (!isset($languages[$data[0]]) || !isset($countries[$data[1]])) {
                    continue;
                }
                $list[$code] = $languages[$data[0]] . ' (' . $countries[$data[1]] . ')';
            }
        }
        asort($list);
        return $list;
    }
}
