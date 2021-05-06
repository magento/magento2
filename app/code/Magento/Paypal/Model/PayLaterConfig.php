<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provides configuration values for PayPal PayLater Banners
 */
class PayLaterConfig
{
    /**
     * Configuration key for Styles settings
     */
    const CONFIG_KEY_STYLE = 'style';

    /**
     * Configuration key for Position setting
     */
    const CONFIG_KEY_POSITION = 'position';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $configData = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Config $config
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
    }

    /**
     * Check if Banner enabled for specified page
     *
     * @param string $placement
     * @return bool
     */
    public function isEnabled(string $placement): bool
    {
        $enabled = false;
        if ($this->isPPCreditEnabled()) {
            $payLaterActive = (boolean)$this->config->getPayLaterConfigValue('experience_active');
            $isPayLaterEnabled = (boolean)$this->config->getPayLaterConfigValue('enabled');
            $enabled = $payLaterActive && $isPayLaterEnabled && $this->getSectionConfig($placement, 'display');
        }
        return $enabled;
    }

    /**
     * Check that PayPal Credit enabled with any PayPal express method
     *
     * @return
     */
    private function isPPCreditEnabled()
    {
        return $this->config->isMethodAvailable(Config::METHOD_WPP_BML)
            || $this->config->isMethodAvailable(Config::METHOD_WPS_BML)
            || $this->config->isMethodAvailable(Config::METHOD_WPP_PE_BML);
    }

    /**
     * Get config for a specific section and key
     *
     * @param string $section
     * @param string $key
     * @return array|string|int
     */
    public function getSectionConfig(string $section, string $key)
    {
        if (!array_key_exists($section, $this->configData)) {
            $this->configData[$section] = [
                'display' => (boolean)$this->config->getPayLaterConfigValue("${section}page_display"),
                'position' => $this->config->getPayLaterConfigValue("${section}page_position"),
                'style' => [
                    'data-pp-style-layout' => $this->config->getPayLaterConfigValue(
                        "${section}page_stylelayout"
                    ),
                    'data-pp-style-logo-type' => $this->config->getPayLaterConfigValue(
                        "${section}page_logotype"
                    ),
                    'data-pp-style-logo-position' => $this->config->getPayLaterConfigValue(
                        "${section}page_logoposition"
                    ),
                    'data-pp-style-text-color' => $this->config->getPayLaterConfigValue(
                        "${section}page_textcolor"
                    ),
                    'data-pp-style-text-size' => $this->config->getPayLaterConfigValue(
                        "${section}page_textsize"
                    ),
                    'data-pp-style-color' => $this->config->getPayLaterConfigValue(
                        "${section}page_color"
                    ),
                    'data-pp-style-ratio' => $this->config->getPayLaterConfigValue(
                        "${section}page_ratio"
                    )
                ]
            ];
        }

        return $this->configData[$section][$key];
    }
}
