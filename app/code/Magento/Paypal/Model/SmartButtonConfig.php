<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Framework\Locale\ResolverInterface;

/**
 * Smart button config
 */
class SmartButtonConfig
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ConfigFactory
     */
    private $config;

    /**
     * @var array
     */
    private $defaultStyles;

    /**
     * @var array
     */
    private $allowedFunding;

    /**
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param array $defaultStyles
     * @param array $allowedFunding
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        $defaultStyles = [],
        $allowedFunding = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->defaultStyles = $defaultStyles;
        $this->allowedFunding = $allowedFunding;
    }

    /**
     * Get smart button config
     *
     * @param string $page
     * @return array
     */
    public function getConfig(string $page): array
    {
        return [
            'merchantId' => $this->config->getValue('merchant_id'),
            'environment' => ((int)$this->config->getValue('sandbox_flag') ? 'sandbox' : 'production'),
            'locale' => $this->localeResolver->getLocale(),
            'allowedFunding' => $this->getAllowedFunding($page),
            'disallowedFunding' => $this->getDisallowedFunding(),
            'styles' => $this->getButtonStyles($page)
        ];
    }

    /**
     * Returns disallowed funding from configuration
     *
     * @return array
     */
    private function getDisallowedFunding(): array
    {
        $disallowedFunding = $this->config->getValue('disable_funding_options');
        return $disallowedFunding ? explode(',', $disallowedFunding) : [];
    }

    /**
     * Returns allowed funding
     *
     * @param string $page
     * @return array
     */
    private function getAllowedFunding(string $page): array
    {
        return array_values(array_diff($this->allowedFunding[$page], $this->getDisallowedFunding()));
    }

    /**
     * Returns button styles based on configuration
     *
     * @param string $page
     * @return array
     */
    private function getButtonStyles(string $page): array
    {
        $styles = $this->defaultStyles[$page];
        if ((boolean)$this->config->getValue("{$page}_page_button_customize")) {
            $styles['layout'] = $this->config->getValue("{$page}_page_button_layout");
            $styles['size'] = $this->config->getValue("{$page}_page_button_size");
            $styles['color'] = $this->config->getValue("{$page}_page_button_color");
            $styles['shape'] = $this->config->getValue("{$page}_page_button_shape");
            $styles['label'] = $this->config->getValue("{$page}_page_button_label");

            $styles = $this->updateStyles($styles, $page);
        }
        return $styles;
    }

    /**
     * Update styles based on locale and labels
     *
     * @param array $styles
     * @param string $page
     * @return array
     */
    private function updateStyles(array $styles, string $page): array
    {
        $locale = $this->localeResolver->getLocale();

        $installmentPeriodLocale = [
            'en_MX' => 'mx',
            'es_MX' => 'mx',
            'en_BR' => 'br',
            'pt_BR' => 'br'
        ];

        // Credit label cannot be used with any custom color option or vertical layout.
        if ($styles['label'] === 'credit') {
            $styles['color'] = 'darkblue';
            $styles['layout'] = 'horizontal';
        }

        // Installment label is only available for specific locales
        if ($styles['label'] === 'installment') {
            if (array_key_exists($locale, $installmentPeriodLocale)) {
                $styles['installmentperiod'] = (int)$this->config->getValue(
                    $page .'_page_button_' . $installmentPeriodLocale[$locale] . '_installment_period'
                );
            } else {
                $styles['label'] = 'paypal';
            }
        }

        return $styles;
    }
}
