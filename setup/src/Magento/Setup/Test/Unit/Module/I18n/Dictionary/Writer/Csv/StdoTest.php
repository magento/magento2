<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer\Csv;

use Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo;

class StdoTest extends \PHPUnit\Framework\TestCase
{
    public function testThatHandlerIsRight()
    {
        $writer = new Stdo();
        $this->assertAttributeEquals(STDOUT, '_fileHandler', $writer);
    }
}
