<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Request\SettlementDataBuilder;

class SettlementDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $this->assertEquals(
            [
                'options' => [
                    SettlementDataBuilder::SUBMIT_FOR_SETTLEMENT => true
                ]
            ],
            (new SettlementDataBuilder())->build([])
        );
    }
}
