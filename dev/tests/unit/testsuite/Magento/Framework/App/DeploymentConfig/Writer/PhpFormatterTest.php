<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig\Writer;

class PhpFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $formatter = new PhpFormatter();
        $data = 'test';
        $this->assertEquals("<?php\nreturn 'test';\n", $formatter->format($data));
    }
}
