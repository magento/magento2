<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
