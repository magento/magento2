<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Service;

use Magento\Catalog\Model\Pricing\SpecialPriceService;
use PHPUnit\Framework\TestCase;

/**
 * Test for SpecialPriceService
 */
class SpecialPriceServiceTest extends TestCase
{
    /**
     * @var SpecialPriceService
     */
    private SpecialPriceService $specialPriceService;

    /**
     * Set up a test environment
     */
    protected function setUp(): void
    {
        $this->specialPriceService = new SpecialPriceService();
    }

    /**
     * Data provider for execute method test
     *
     * @return array
     */
    public static function executeDataProvider(): array
    {
        return [
            'invalid_date' => [
                'dateTo' => 'some date to',
                'expected' => 'some date to'
            ],
            'date_without_time' => [
                'dateTo' => '2025-05-12 00:00:00',
                'expected' => '2025-05-12 00:00:00'
            ],
            'date_with_specific_time' => [
                'dateTo' => '2025-05-12 17:00:00',
                'expected' => '2025-05-11 17:00:00'
            ]
        ];
    }

    /**
     * @dataProvider executeDataProvider
     * @param mixed $dateTo
     * @param mixed $expected
     */
    public function testExecute(mixed $dateTo, mixed $expected): void
    {
        $result = $this->specialPriceService->execute($dateTo);
        $this->assertEquals($expected, $result);
    }
}
