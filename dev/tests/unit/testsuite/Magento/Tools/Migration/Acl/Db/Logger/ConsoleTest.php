<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\Acl\Db\Logger;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/AbstractLogger.php';

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/Acl/Db/Logger/Console.php';
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testReport()
    {
        $this->expectOutputRegex('/^Mapped items count: 0(.)*/');
        $model = new \Magento\Tools\Migration\Acl\Db\Logger\Console();
        $model->report();
    }
}
