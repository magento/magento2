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
