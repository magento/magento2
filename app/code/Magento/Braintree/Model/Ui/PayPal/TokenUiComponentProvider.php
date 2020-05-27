<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui\PayPal;

use Magento\Braintree\Model\Ui\ConfigProvider as CommonConfigProvider;
use Magento\Framework\UrlInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

/**
 * Class TokenUiComponentProvider
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
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        UrlInterface $urlBuilder
    ) {
        $this->componentFactory = $componentFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::PAYPAL_VAULT_CODE,
                    'nonceUrl' => $this->getNonceRetrieveUrl(),
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'Magento_Braintree/js/view/payment/method-renderer/paypal-vault'
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
        return $this->urlBuilder->getUrl(CommonConfigProvider::CODE . '/payment/getnonce', ['_secure' => true]);
    }
}
