<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class SettlementDataBuilder
 *
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class SettlementDataBuilder implements BuilderInterface
{
    const SUBMIT_FOR_SETTLEMENT = 'submitForSettlement';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            'options' => [
                self::SUBMIT_FOR_SETTLEMENT => true
            ]
        ];
    }
}
