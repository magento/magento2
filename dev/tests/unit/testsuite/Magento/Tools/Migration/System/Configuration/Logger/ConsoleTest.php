<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration\Logger;

require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/AbstractLogger.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Configuration/Logger/Console.php';
class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    public function testReport()
    {
        $this->expectOutputRegex('/^valid: 0(.)*/');
        $model = new \Magento\Tools\Migration\System\Configuration\Logger\Console();
        $model->report();
    }
}
