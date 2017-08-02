<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

/**
 * Interface for classes that return array of locales.
 * @since 2.2.0
 */
interface OptionInterface
{
    /**
     * Get array of deployed locales.
     *
     * Function result has next format:
     * ```php
     *     [
     *        0 => [
     *           'value' => 'de_DE'
     *           'label' => 'German (Germany)'
     *        ],
     *        1 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom)'
     *        ],
     *    ]
     * ```
     *
     * @return array
     * @since 2.2.0
     */
    public function getOptionLocales();

    /**
     * Get array of deployed locales with translation.
     *
     * Function result has next format:
     * ```php
     *     [
     *        0 => [
     *           'value' => 'de_DE'
     *           'label' => 'Deutsch (Deutschland) / German (Germany)'
     *        ],
     *        1 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom) / English (United Kingdom)'
     *        ],
     *    ]
     * ```
     *
     * @return array
     * @since 2.2.0
     */
    public function getTranslatedOptionLocales();
}
