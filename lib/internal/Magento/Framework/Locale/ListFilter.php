<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

use Magento\Framework\App\State;
use Magento\Theme\Model\View\Design;

/**
 * Filters given list of locales by available locales for current loaded theme.
 */
class ListFilter implements ListFilterInterface
{
    /**
     * @var State
     */
    private $state;

    /**
     * @var AvailableLocalesInterface
     */
    private $availableLocales;

    /**
     * @var Design
     */
    private $design;

    /**
     * @param State $state
     * @param AvailableLocalesInterface $availableLocales
     * @param Design $design
     */
    public function __construct(
        State $state,
        AvailableLocalesInterface $availableLocales,
        Design $design
    ) {
        $this->state = $state;
        $this->availableLocales = $availableLocales;
        $this->design = $design;
    }

    /**
     * Filter list of locales by available locales for current loaded theme.
     *
     * For example, if the current theme has only one generated locale en_GB then for given array of locales:
     * ```php
     *     $locales = [
     *        0 => [
     *           'value' => 'da_DK'
     *           'label' => 'Dansk (Danmark) / Danish (Denmark)'
     *        ],
     *        1 => [
     *           'value' => 'de_DE'
     *           'label' => 'Deutsch (Deutschland) / German (Germany)'
     *        ],
     *        2 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom) / English (United Kingdom)'
     *        ],
     *    ]
     * ```
     * result will be:
     * ```php
     *    [
     *        2 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom) / English (United Kingdom)'
     *        ],
     *    ]
     * ```
     *
     * @param array $locales list of locales for filtering
     * @return array of filtered locales
     */
    public function filter(array $locales)
    {
        if ($this->state->getMode() == State::MODE_PRODUCTION) {
            return $locales;
        }

        $theme = $this->design->getDesignTheme();
        $availableLocales = $this->availableLocales->getList($theme->getCode(), $theme->getArea());

        return array_filter($locales, function($localeData) use ($availableLocales) {
            return in_array($localeData['value'], $availableLocales);
        });
    }
}
