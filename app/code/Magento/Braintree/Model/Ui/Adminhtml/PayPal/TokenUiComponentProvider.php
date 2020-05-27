<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui\Adminhtml\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PayPalConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Gets Ui component configuration for Braintree PayPal Vault
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{

    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param UrlInterface $urlBuilder
     * @param Config $config
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $data = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $data['icon'] = $this->config->getPayPalIcon();
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => PayPalConfigProvider::PAYPAL_VAULT_CODE,
                    'nonceUrl' => $this->getNonceRetrieveUrl(),
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $data,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash(),
                    'template' => 'Magento_Braintree::form/paypal/vault.phtml'
                ],
                'name' => Template::class
            ]
        );

        return $component;
    }

    /**
     * Get url to retrieve payment method nonce
     * @return string
     */
    private function getNonceRetrieveUrl()
    {
        return $this->urlBuilder->getUrl(ConfigProvider::CODE . '/payment/getnonce', ['_secure' => true]);
    }
}
