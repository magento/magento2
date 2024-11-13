<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Claim;

use Magento\Framework\Jwt\Claim\AbstractClaim;
use PHPUnit\Framework\TestCase;

class AbstractClaimTest extends TestCase
{
    /**
     * Test parsing NumericDate JS format.
     *
     * @param string $numericDate
     * @param string $expectedTime
     * @param string $expectedZone
     * @return void
     *
     * @dataProvider getDates
     */
    public function testParseNumericDate(string $numericDate, string $expectedTime): void
    {
        $dt = AbstractClaim::parseNumericDate($numericDate);
        $this->assertEquals($expectedTime, $dt->format('Y-m-d H:i:s'));
        $this->assertEquals('UTC', $dt->getTimezone()->getName());
    }

    public static function getDates(): array
    {
        return [
            ['1970-01-01T00:00:00Z', '1970-01-01 00:00:00'],
            ['1996-12-19T16:39:57-08:00', '1996-12-20 00:39:57']
        ];
    }
}
