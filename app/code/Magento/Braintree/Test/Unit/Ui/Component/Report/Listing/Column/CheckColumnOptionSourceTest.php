<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Ui\Component\Report\Listing\Column;

use Magento\Braintree\Ui\Component\Report\Listing\Column\PaymentType;
use Magento\Braintree\Ui\Component\Report\Listing\Column\Status;
use Magento\Braintree\Ui\Component\Report\Listing\Column\TransactionType;
use PHPUnit\Framework\TestCase;

class CheckColumnOptionSourceTest extends TestCase
{
    public function testPaymentTypeSource()
    {
        $source = new PaymentType();
        $options = $source->toOptionArray();

        static::assertCount(6, $options);
    }

    public function testStatusSource()
    {
        $source = new Status();
        $options = $source->toOptionArray();

        static::assertCount(14, $options);
    }

    public function testTransactionTypeSource()
    {
        $source = new TransactionType();
        $options = $source->toOptionArray();

        static::assertCount(2, $options);
    }
}
