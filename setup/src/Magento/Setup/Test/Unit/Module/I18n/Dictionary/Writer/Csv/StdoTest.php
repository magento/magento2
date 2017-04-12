<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer\Csv;

use Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo;

class StdoTest extends \PHPUnit_Framework_TestCase
{
    public function testThatHandlerIsRight()
    {
        $handler = STDOUT;
        // Mocking object's under test destructor here is perfectly valid as there is no way to reopen STDOUT
        $writer = $this->getMock(Stdo::class, ['__destruct']);
        $this->assertAttributeEquals($handler, '_fileHandler', $writer);
    }
}
