<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Block\Form\Container;
use Magento\Vault\Model\Ui\Adminhtml\TokensConfigProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class PaymentMethodProcess
{
    /**
     * @var string
     */
    private string $braintreeCCVault;

    /**
     * @var TokensConfigProvider
     */
    private TokensConfigProvider $tokensConfigProvider;

    /**
     * @param string $braintreeCCVault
     * @param TokensConfigProvider|null $tokensConfigProvider
     */
    public function __construct(
        string $braintreeCCVault = '',
        TokensConfigProvider $tokensConfigProvider = null
    ) {
        $this->braintreeCCVault = $braintreeCCVault;
        $this->tokensConfigProvider = $tokensConfigProvider ??
            ObjectManager::getInstance()->get(TokensConfigProvider::class);
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
            if ($result->getCode() === $this->braintreeCCVault
                && empty($this->tokensConfigProvider->getTokensComponents($result->getCode()))) {

                continue;
            }
            $methods[] = $result;
        }
        return $methods;
    }
}
