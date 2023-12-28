<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\Stdlib\ArrayManager;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Get payment additional data for Payflow pro cc vault payment
 */
class PayflowProCcVaultAdditionalDataProvider implements AdditionalDataProviderInterface
{
    public const CC_VAULT_CODE = 'payflowpro_cc_vault';

    /**
     * @param ArrayManager $arrayManager
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    public function __construct(ArrayManager $arrayManager)
    {
    }

    /**
     * Returns additional data
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (isset($args[self::CC_VAULT_CODE])) {
            return $args[self::CC_VAULT_CODE];
        }
        return [];
    }
}
