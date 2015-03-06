<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Translate\Js;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $config = new Config();
        $this->assertFalse($config->dictionaryEnabled());
        $this->assertNull($config->getDictionaryFileName());
    }

    public function testCustom()
    {
        $path = 'path';
        $config = new Config(true, $path);
        $this->assertTrue($config->dictionaryEnabled());
        $this->assertEquals($path, $config->getDictionaryFileName());
    }
}
