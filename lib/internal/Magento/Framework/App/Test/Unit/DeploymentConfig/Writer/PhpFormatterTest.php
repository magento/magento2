<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\DeploymentConfig\Writer;

use \Magento\Framework\App\DeploymentConfig\Writer\PhpFormatter;

class PhpFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new PhpFormatter();
        $data = 'test';
        $this->assertEquals("<?php\nreturn 'test';\n", $formatter->format($data));
    }
}
