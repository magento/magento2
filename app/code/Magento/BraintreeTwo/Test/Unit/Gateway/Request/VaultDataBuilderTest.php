<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Test\Unit\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Request\VaultDataBuilder;

class VaultDataBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $expectedResult = [
            VaultDataBuilder::OPTIONS => [
                VaultDataBuilder::STORE_IN_VAULT_ON_SUCCESS => true
            ]
        ];

        $buildSubject = [];

        $builder = new VaultDataBuilder();
        static::assertEquals(
            $expectedResult,
            $builder->build($buildSubject)
        );
    }
}
