<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Test\Unit\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Request\AmountDataBuilder;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use PHPUnit\Framework\TestCase;

class AmountDataBuilderTest extends TestCase
{
    /**
     * @var AmountDataBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new AmountDataBuilder(
            new SubjectReader()
        );
    }

    public function testBuild()
    {
        $expected = [
            'transactionRequest' => [
                'amount' => '123.45',
            ]
        ];

        $buildSubject = [
            'amount' => 123.45
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
