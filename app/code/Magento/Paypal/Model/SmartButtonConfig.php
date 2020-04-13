<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model;

use Magento\Checkout\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Paypal\Model\Config as PayPalConfig;

/**
 * Provides configuration values for PayPal in-context checkout
 *
 * Class SmartButtonConfig
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Maps the old checkout SDK configuration values to the current ones
     * @var array
     */
    private $disallowedFundingMap;

    /**
     * These payment methods will be added as parameters to the SDK url to disable them.
     * @var array
     */
    private $unsupportedPaymentMethods;

    /**
     * Base url for Paypal SDK
     */
    private const BASE_URL = 'https://www.paypal.com/sdk/js?';

    /**
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $defaultStyles
     * @param array $disallowedFundingMap
     * @param array $unsupportedPaymentMethods
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        $defaultStyles = [],
        $disallowedFundingMap = [],
        $unsupportedPaymentMethods = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->scopeConfig = $scopeConfig;
        $this->defaultStyles = $defaultStyles;
        $this->disallowedFundingMap = $disallowedFundingMap;
        $this->unsupportedPaymentMethods = $unsupportedPaymentMethods;
    }

    /**
     * Get smart button config
     *
     * @param string $page
     * @return array
     */
    public function getConfig(string $page): array
    {
        $isGuestCheckoutAllowed = $this->scopeConfig->isSetFlag(
            Data::XML_PATH_GUEST_CHECKOUT,
            ScopeInterface::SCOPE_STORE
        );

        return [
            'styles' => $this->getButtonStyles($page),
            'isVisibleOnProductPage'  => (bool)$this->config->getValue('visible_on_product'),
            'isGuestCheckoutAllowed'  => $isGuestCheckoutAllowed,
            'sdkUrl' => $this->generatePaypalSdkUrl()
        ];
    }

    /**
     * Generate the url to download the Paypal SDK
     *
     * @return string
     */
    private function generatePaypalSdkUrl() : string
    {
        $clientId = (int)$this->config->getValue('sandbox_flag') ?
            $this->config->getValue('sandbox_client_id') : $this->config->getValue('client_id');
        $disallowedFunding = implode(",", $this->getDisallowedFunding());

        $params =
            [
                'client-id' => $clientId,
                'commit' => 'false',
                'merchant-id' => $this->config->getValue('merchant_id'),
                'locale' => $this->localeResolver->getLocale(),
                'intent' => $this->getIntent(),
            ];
        if ($disallowedFunding) {
            $params['disable-funding'] =  $disallowedFunding;
        }

        return self::BASE_URL . http_build_query($params);
    }

    /**
     * Return intent value from the configuration payment_action value
     *
     * @return string
     */
    private function getIntent(): string
    {
        $paymentAction = $this->config->getValue('paymentAction');
        $mappedIntentValues = [
            Config::PAYMENT_ACTION_AUTH => 'authorize',
            Config::PAYMENT_ACTION_SALE => 'capture',
            Config::PAYMENT_ACTION_ORDER => 'order'
        ];
        return $mappedIntentValues[$paymentAction];
    }

    /**
     * Returns disallowed funding from configuration after updating values
     *
     * @return array
     */
    private function getDisallowedFunding(): array
    {
        $disallowedFundingConfig = $this->config->getValue('disable_funding_options');
        $disallowedFundingArray = $disallowedFundingConfig ? explode(',', $disallowedFundingConfig) : [];

        //Disable "card" method if guest PayPal checkout is disabled
        $isGuestPayPalCheckoutEnabled
            = $this->config->getValue('solution_type') === PayPalConfig::EC_SOLUTION_TYPE_SOLE;
        $disallowedFundingArray = !$isGuestPayPalCheckoutEnabled && !in_array('CARD', $disallowedFundingArray)
            ? array_merge_recursive(['CARD'], $disallowedFundingArray)
            : $disallowedFundingArray;

        // Map old configuration values to current ones
        $disallowedFundingArray = array_map(function ($oldValue) {
            return $this->disallowedFundingMap[$oldValue] ?? $oldValue;
        },
            $disallowedFundingArray);

        //disable unsupported payment methods
        $disallowedFundingArray = array_merge($disallowedFundingArray, $this->unsupportedPaymentMethods);

        return $disallowedFundingArray;
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
