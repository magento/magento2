<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Ui;

use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    protected $componentFactory;

    /**
     * @param TokenUiComponentInterfaceFactory $componentFactory
     */
    public function __construct(TokenUiComponentInterfaceFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
    }

    /**
     * Get UI component for token
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        /** @var TokenUiComponentInterface $component */
        $component = $this->componentFactory->create(
            [
                'config' => [
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => json_decode(
                        $paymentToken->getTokenDetails() ?: '{}', true
                    ),
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'Magento_Vault/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
