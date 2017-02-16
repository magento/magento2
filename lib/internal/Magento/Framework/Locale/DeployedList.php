<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Locale;

use Magento\Framework\App\State;
use Magento\Framework\View\DesignInterface;

/**
 * Returns options array of locales that have deployed static content.
 */
class DeployedList implements DeployedListInterface
{
    /**
     * Application state class.
     *
     * @var State
     */
    private $state;

    /**
     * Operates with available locales.
     *
     * @var AvailableLocalesInterface
     */
    private $availableLocales;

    /**
     * @var DesignInterface
     */
    private $design;

    /**
     * Locales lists.
     *
     * @var ListsInterface
     */
    private $localeLists;

    /**
     * @param ListsInterface $localeLists
     * @param State $state
     * @param AvailableLocalesInterface $availableLocales
     * @param DesignInterface $design
     */
    public function __construct(
        ListsInterface $localeLists,
        State $state,
        AvailableLocalesInterface $availableLocales,
        DesignInterface $design
    ) {
        $this->localeLists = $localeLists;
        $this->state = $state;
        $this->availableLocales = $availableLocales;
        $this->design = $design;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        return $this->filterLocales($this->localeLists->getOptionLocales());
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslatedLocales()
    {
        return $this->filterLocales($this->localeLists->getTranslatedOptionLocales());
    }

    /**
     * Filter list of locales by available locales for current theme and depends on running application mode.
     *
     * Applies filters only in production mode.
     * For example, if the current design theme has only one generated locale en_GB then for given array of locales:
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
    private function filterLocales(array $locales)
    {
        if ($this->state->getMode() != State::MODE_PRODUCTION) {
            return $locales;
        }

        $theme = $this->design->getDesignTheme();
        $availableLocales = $this->availableLocales->getList($theme->getCode(), $theme->getArea());

        return array_filter($locales, function($localeData) use ($availableLocales) {
            return in_array($localeData['value'], $availableLocales);
        });
    }
}
