<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds the meta transaction information to the request
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RefundTransactionTypeDataBuilder implements BuilderInterface
{
    private const REQUEST_TYPE_REFUND = 'refundTransaction';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        return [
            'transactionRequest' => [
                'transactionType' => self::REQUEST_TYPE_REFUND
            ]
        ];
    }
}
