<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Validator\Test\Unit;

class TimezoneTest extends \PHPUnit_Framework_TestCase
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
        $lists = $this->getMock('Magento\Framework\Setup\Lists', [], [], '', false);
        $lists->expects($this->any())->method('getTimezoneList')->will($this->returnValue($this->expectedTimezones));
        $timezone = new \Magento\Framework\Validator\Timezone($lists);
        $this->assertEquals(true, $timezone->isValid('America/Los_Angeles'));
    }
}
