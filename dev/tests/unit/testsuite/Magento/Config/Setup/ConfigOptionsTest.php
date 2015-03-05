<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Setup;

class ConfigOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigOptions
     */
    private $object;

    protected function setUp()
    {
        $this->object = new ConfigOptions();
    }

    public function testGetOptions()
    {
        $options = $this->object->getOptions();
        $this->assertEquals(0, count($options));
    }

    public function testDefaultCreateConfig()
    {
        $options = [];
        $expected = [ConfigOptions::CONFIG_PATH_RESOURCE =>['default_setup' => ['connection' => 'default']]];
        $this->assertSame($expected, $this->object->createConfig($options));
    }

    public function testCreateConfigWithInput()
    {
        $options[ConfigOptions::INPUT_KEY_RESOURCE] = ['test' => ['name' =>'test', 'connection' => 'test']];
        $expected = $options;
        $this->assertSame($expected, $this->object->createConfig($options));
    }

}
