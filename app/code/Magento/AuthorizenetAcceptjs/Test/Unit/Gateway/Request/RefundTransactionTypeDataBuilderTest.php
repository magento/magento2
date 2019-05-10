<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\RefundTransactionTypeDataBuilder;
use PHPUnit\Framework\TestCase;

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Request\RefundTransactionTypeDataBuilder
 */
class RefundTransactionTypeDataBuilderTest extends TestCase
{
    /**
     * @var string
     */
    private $requestTypeRefund = 'refundTransaction';

    /**
     * @return void
     */
    public function testBuild()
    {
        $builder = new RefundTransactionTypeDataBuilder();

        $expected = [
            'transactionRequest' => [
                'transactionType' => $this->requestTypeRefund,
            ],
        ];

        $this->assertEquals($expected, $builder->build([]));
    }
}
