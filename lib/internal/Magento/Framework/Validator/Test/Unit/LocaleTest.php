<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit;

use Magento\Framework\Setup\Lists;
use Magento\Framework\Validator\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    /**
     * @var array
     */
    protected $expectedLocales = [
        'en_US' => 'English (United States)',
        'en_GB' => 'English (United Kingdom)',
        'uk_UA' => 'Ukrainian (Ukraine)',
        'de_DE' => 'German (Germany)'
    ];

    public function testIsValid()
    {
        $lists = $this->createMock(Lists::class);
        $lists->expects($this->any())->method('getLocaleList')->willReturn($this->expectedLocales);
        $locale = new Locale($lists);
        $this->assertTrue($locale->isValid('en_US'));
    }
}
