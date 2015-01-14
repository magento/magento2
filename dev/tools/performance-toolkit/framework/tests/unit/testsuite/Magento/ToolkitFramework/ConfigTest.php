<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
