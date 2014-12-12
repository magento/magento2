<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
