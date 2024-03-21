<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Vault\Plugin;

use Magento\Payment\Block\Form\Container;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;
use Magento\Vault\Model\VaultPaymentInterface;

class PaymentMethodProcess
{
    /**
     * @var TokensConfigProvider
     */
    private TokensConfigProvider $tokensConfigProvider;

    /**
     * @param TokensConfigProvider $tokensConfigProvider
     */
    public function __construct(
        TokensConfigProvider $tokensConfigProvider
    ) {
        $this->tokensConfigProvider = $tokensConfigProvider;
    }

    /**
     * Retrieve available payment methods
     *
     * @param Container $container
     * @param array $results
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMethods(Container $container, array $results): array
    {
        $methods = [];
        foreach ($results as $result) {
            if ($result instanceof VaultPaymentInterface &&
                empty($this->tokensConfigProvider->getTokensComponents($result->getCode()))) {
                continue;
            }
            $methods[] = $result;
        }
        return $methods;
    }
}
