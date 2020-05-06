<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Validator\Timezone;
use PHPUnit\Framework\TestCase;

class TimezoneTest extends TestCase
{
    /**
     * @var array
     */
    protected $expectedTimezones = [
        'Australia/Darwin',
        'America/Los_Angeles',
        'Europe/Kiev',
        'Asia/Jerusalem',
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(Lists::class);
        $lists->expects($this->any())->method('getTimezoneList')->willReturn($this->expectedTimezones);
        $timezone = new Timezone($lists);
        $this->assertTrue($timezone->isValid('America/Los_Angeles'));
    }
}
