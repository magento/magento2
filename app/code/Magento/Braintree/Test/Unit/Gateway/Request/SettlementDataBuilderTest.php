<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Test\Unit\Gateway\Request;

use Magento\Braintree\Gateway\Request\SettlementDataBuilder;
use PHPUnit\Framework\TestCase;

class SettlementDataBuilderTest extends TestCase
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
