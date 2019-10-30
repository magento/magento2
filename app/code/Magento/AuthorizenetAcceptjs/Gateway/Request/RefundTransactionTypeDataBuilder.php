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
 * @deprecated Starting from Magento 2.2.11 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class RefundTransactionTypeDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $requestTypeRefund = 'refundTransaction';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            'transactionRequest' => [
                'transactionType' => self::$requestTypeRefund,
            ],
        ];
    }
}
