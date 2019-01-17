<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\RefundTransactionTypeDataBuilder;

class RefundTransactionTypeDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    private const REQUEST_TYPE_REFUND = 'refundTransaction';

    public function testBuild()
    {
        $builder = new RefundTransactionTypeDataBuilder();

        $expected = [
            'transactionRequest' => [
                'transactionType' => self::REQUEST_TYPE_REFUND
            ]
        ];

        $this->assertEquals($expected, $builder->build([]));
    }
}
