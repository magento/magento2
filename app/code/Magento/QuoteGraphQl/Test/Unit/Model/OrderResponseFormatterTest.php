<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model;

use Magento\QuoteGraphQl\Model\OrderResponseFormatter;
use PHPUnit\Framework\TestCase;

class OrderResponseFormatterTest extends TestCase
{
    /**
     * @return void
     */
    public function testNotApplicable(): void
    {
        $formatter = new OrderResponseFormatter();
        $executionResult = ['data' => []];

        $result = $formatter->formatResponse($executionResult);

        $this->assertEquals($executionResult, $result);
    }

    /**
     * @return void
     */
    public function testFormattingIsApplied(): void
    {
        $formatter = new OrderResponseFormatter();
        $executionResult = [
            'errors' => [
                [
                    'message' => 'An error occurred',
                    'extensions' => [
                        'error_code' => 'some_error_code',
                    ],
                ],
            ],
            'data' => [
                'placeOrder' => null,
            ],
        ];

        $result = $formatter->formatResponse($executionResult);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('placeOrder', $result['data']);
        $this->assertArrayHasKey('errors', $result['data']['placeOrder']);
        $this->assertCount(1, $result['data']['placeOrder']['errors']);
        $this->assertEquals('some_error_code', $result['data']['placeOrder']['errors'][0]['code']);
    }
}
