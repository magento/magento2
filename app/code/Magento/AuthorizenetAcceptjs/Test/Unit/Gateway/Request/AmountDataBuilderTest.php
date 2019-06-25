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

/**
 * Test for Magento\AuthorizenetAcceptjs\Gateway\Request\AmountDataBuilder
 */
class AmountDataBuilderTest extends TestCase
{
    /**
     * @var AmountDataBuilder
     */
    private $builder;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->builder = new AmountDataBuilder(
            new SubjectReader()
        );
    }

    /**
     * @return void
     */
    public function testBuild()
    {
        $expected = [
            'transactionRequest' => [
                'amount' => '123.45',
            ],
        ];

        $buildSubject = [
            'amount' => 123.45,
        ];

        $this->assertEquals($expected, $this->builder->build($buildSubject));
    }
}
