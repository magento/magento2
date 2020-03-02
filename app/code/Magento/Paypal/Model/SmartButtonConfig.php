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
     * @var array
     */
    private $disallowedFundingMap;

    /**
     * Base url for Paypal SDK
     */
    const BASE_URL = 'https://www.paypal.com/sdk/js?';

    /**
     * @param ResolverInterface $localeResolver
     * @param ConfigFactory $configFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $defaultStyles
     * @param array $disallowedFundingMap
     */
    public function __construct(
        ResolverInterface $localeResolver,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        $defaultStyles = [],
        $disallowedFundingMap = []
    ) {
        $this->localeResolver = $localeResolver;
        $this->config = $configFactory->create();
        $this->config->setMethod(Config::METHOD_EXPRESS);
        $this->scopeConfig = $scopeConfig;
        $this->defaultStyles = $defaultStyles;
        $this->disallowedFundingMap = $disallowedFundingMap;
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

        $isSandbox = (int)$this->config->getValue('sandbox_flag');
        $clientId = $isSandbox ?
            $this->config->getValue('sandbox_client_id') : $this->config->getValue('client_id');
        $merchantId = $this->config->getValue('merchant_id');
        $locale = $this->localeResolver->getLocale();
        $disallowedFunding = implode(",", $this->getDisallowedFunding());

        return [
            'merchantId' => $merchantId,
            'environment' => ( $isSandbox ? 'sandbox' : 'production'),
            'locale' => $locale,
            'styles' => $this->getButtonStyles($page),
            'isVisibleOnProductPage'  => (bool)$this->config->getValue('visible_on_product'),
            'isGuestCheckoutAllowed'  => $isGuestCheckoutAllowed,
            'sdkUrl' => $this->generatePaypalSdkUrl($clientId, $merchantId, $locale, $disallowedFunding)
        ];
    }

    /**
     * Generate the url to download the Paypal SDK
     *
     * @param string $clientId
     * @param string $merchantId
     * @param string $locale
     * @param string $disallowedFunding
     * @return string
     */
    private function generatePaypalSdkUrl(
        string $clientId,
        string $merchantId,
        string $locale,
        string $disallowedFunding
    ) : string {
        $params =
            [
                'client-id' => $clientId,
                'commit' => 'false',
                'merchant-id' => $merchantId,
                'locale' => $locale,
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
        $disallowedFunding = $this->config->getValue('disable_funding_options');
        $isGuestPayPalAvailable = $this->config->getValue('solution_type') === PayPalConfig::EC_SOLUTION_TYPE_SOLE;
        $disallowedFundingArray = $disallowedFunding ? explode(',', $disallowedFunding) : [];
        $disallowedFundingArray = !$isGuestPayPalAvailable && !in_array('CARD', $disallowedFundingArray)
            ? array_merge_recursive(['CARD'], $disallowedFundingArray)
            : $disallowedFundingArray;

        // Map old configuration values to current ones
        return array_map(function ($oldValue) {
            return $this->disallowedFundingMap[$oldValue] ?? $oldValue;
        },
            $disallowedFundingArray);
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
