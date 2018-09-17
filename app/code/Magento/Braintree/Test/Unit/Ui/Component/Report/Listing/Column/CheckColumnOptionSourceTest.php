<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Ui\Component\Report\Listing\Column;

use Magento\Braintree\Ui\Component\Report\Listing\Column\PaymentType;
use Magento\Braintree\Ui\Component\Report\Listing\Column\Status;
use Magento\Braintree\Ui\Component\Report\Listing\Column\TransactionType;

/**
 * Class CheckColumnOptionSourceTest
 */
class CheckColumnOptionSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testPaymentTypeSource()
    {
        $source = new PaymentType();
        $options = $source->toOptionArray();

        static::assertEquals(6, count($options));
    }

    public function testStatusSource()
    {
        $source = new Status();
        $options = $source->toOptionArray();

        static::assertEquals(14, count($options));
    }

    public function testTransactionTypeSource()
    {
        $source = new TransactionType();
        $options = $source->toOptionArray();

        static::assertEquals(2, count($options));
    }
}
