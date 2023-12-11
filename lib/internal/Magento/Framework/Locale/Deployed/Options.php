<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Locale\Deployed;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Config\ConfigOptionsListConstants as Constants;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\AvailableLocalesInterface;
use Magento\Framework\Locale\ListsInterface;
use Magento\Framework\Locale\OptionInterface;
use Magento\Framework\View\DesignInterface;

/**
 * Returns options array of locales that have deployed static content.
 */
class Options implements OptionInterface
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
     * Operates with magento design settings.
     *
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
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param ListsInterface $localeLists locales list
     * @param State $state application state class
     * @param AvailableLocalesInterface $availableLocales operates with available locales
     * @param DesignInterface $design operates with magento design settings
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ListsInterface $localeLists,
        State $state,
        AvailableLocalesInterface $availableLocales,
        DesignInterface $design,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->localeLists = $localeLists;
        $this->state = $state;
        $this->availableLocales = $availableLocales;
        $this->design = $design;
        $this->deploymentConfig = $deploymentConfig ?: ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptionLocales(): array
    {
        return $this->filterLocales($this->localeLists->getOptionLocales());
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslatedOptionLocales(): array
    {
        return $this->filterLocales($this->localeLists->getTranslatedOptionLocales());
    }

    /**
     * Filter list of locales by available locales for current theme and depends on running application mode.
     *
     * Applies filters only in production mode when flag 'static_content_on_demand_in_production' is not enabled.
     * For example, if the current design theme has only one generated locale en_GB then for given array of locales:
     * ```php
     *     $locales = [
     *        0 => [
     *           'value' => 'da_DK'
     *           'label' => 'Danish (Denmark)'
     *        ],
     *        1 => [
     *           'value' => 'de_DE'
     *           'label' => 'German (Germany)'
     *        ],
     *        2 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom)'
     *        ],
     *    ]
     * ```
     * result will be:
     * ```php
     *    [
     *        2 => [
     *           'value' => 'en_GB'
     *           'label' => 'English (United Kingdom)'
     *        ],
     *    ]
     * ```
     *
     * @param array $locales list of locales for filtering
     * @return array of filtered locales
     */
    private function filterLocales(array $locales): array
    {
        if ($this->state->getMode() != State::MODE_PRODUCTION
            || $this->deploymentConfig->getConfigData(Constants::CONFIG_PATH_SCD_ON_DEMAND_IN_PRODUCTION)) {
            return $locales;
        }

        $theme = $this->design->getDesignTheme();
        try {
            $availableLocales = $this->availableLocales->getList($theme->getCode(), $theme->getArea());
        } catch (LocalizedException $e) {
            $availableLocales = [];
        }

        return array_filter($locales, function ($localeData) use ($availableLocales) {
            return in_array($localeData['value'], $availableLocales);
        });
    }
}
