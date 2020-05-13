<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n\Dictionary\Writer\Csv;

use Magento\Setup\Module\I18n\Dictionary\Writer\Csv\Stdo;
use PHPUnit\Framework\TestCase;

class StdoTest extends TestCase
{
    public function testThatHandlerIsRight()
    {
        $this->markTestSkipped('Skipped in #27500 due to testing protected/private methods and properties');
        $writer = new Stdo();
        $this->assertAttributeEquals(STDOUT, '_fileHandler', $writer);
    }
}
