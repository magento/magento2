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
 * @since 2.2.0
 */
class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{

    /**
     * @var TokenUiComponentInterfaceFactory
     * @since 2.2.0
     */
    private $componentFactory;

    /**
     * @var UrlInterface
     * @since 2.2.0
     */
    private $urlBuilder;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param UrlInterface $urlBuilder
     * @param Config $config
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function getNonceRetrieveUrl()
    {
        return $this->urlBuilder->getUrl(ConfigProvider::CODE . '/payment/getnonce', ['_secure' => true]);
    }
}
