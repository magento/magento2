<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\SettlementDataBuilder;

class SettlementDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        self::assertEquals(
            [
                'options' => [
                    SettlementDataBuilder::SUBMIT_FOR_SETTLEMENT => true
                ]
            ],
            (new SettlementDataBuilder())->build([])
        );
    }
}
