<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

class LocaleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $expectedLocales = [
        'en_US',
        'en_GB',
        'uk_UA',
        'de_DE',
    ];

    public function testIsValid()
    {
        $lists = $this->getMock('Magento\Framework\Setup\Lists', [], [], '', false);
        $lists->expects($this->any())->method('getLocaleList')->will($this->returnValue($this->expectedLocales));
        $locale = new \Magento\Framework\Validator\Locale($lists);
        $this->assertEquals(true, $locale->isValid('en_US'));
    }
}
