<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;

/**
 * Get payment additional data for Payflow pro payment
 */
class PayflowProCcVaultAdditionalDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'payflowpro_cc_vault';
    /**
     * Format Payflow input into value expected when setting payment method
     *
     * @param array $args
     * @return array
     */
    public function getData(array $args): array
    {
        if (!isset($args[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "payflowpro_cc_vault" for "payment_method" is missing.')
            );
        }

        if (!isset($args[self::PATH_ADDITIONAL_DATA]['public_hash'])) {
            throw new GraphQlInputException(
                __('Required parameter "public_hash" for "payflowpro_cc_vault" is missing.')
            );
        }

        return $args[self::PATH_ADDITIONAL_DATA];
    }
}
