<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale;

/**
 * Interface for classes that return array of locales.
 *
 * @api
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
     */
    public function getTranslatedOptionLocales();
}
