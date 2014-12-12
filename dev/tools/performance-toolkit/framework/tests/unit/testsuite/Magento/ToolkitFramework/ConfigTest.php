<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ToolkitFramework;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     */
    public function testUnaccessibleConfig()
    {
        $this->setExpectedException('Exception', 'Profile configuration file `))` is not readable or does not exists.');
        \Magento\ToolkitFramework\Config::getInstance()->loadConfig('))');
    }
}
